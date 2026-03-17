<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Analyser;
use Bunnivo\Soda\Breathing\BreathingAnalyser;
use Bunnivo\Soda\ParserException;
use Bunnivo\Soda\Quality\Config\ConfigResolver;
use Bunnivo\Soda\Quality\EvaluationContext\MethodMetricsData;
use Bunnivo\Soda\Quality\EvaluationContext\MethodNestingReturns;

use function file_get_contents;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use Bunnivo\Soda\Complexity\EnumAwareComplexityVisitor;
use SebastianBergmann\Complexity\ComplexityCollection;

use function substr_count;

final class QualityAnalyser
{
    /**
     * @psalm-param list<non-empty-string> $files
     * @psalm-param non-empty-string|null $configPath
     *
     * @throws ConfigException
     */
    public function analyse(array $files, bool $debug, ?string $configPath = null): QualityResult
    {
        $analyser = new Analyser();
        $result = $analyser->analyse($files, $debug);

        $qualityMetrics = [];
        $complexityByMethod = [];
        $nestingByMethod = [];
        $returnsByMethod = [];
        $booleanConditionsByMethod = [];

        foreach ($files as $file) {
            try {
                $fileResult = $this->analyseFile($file);

                $qualityMetrics[$file] = $fileResult['metrics'];

                foreach ($fileResult['complexity']->asArray() as $item) {
                    $complexityByMethod[$item->name()] = $item->cyclomaticComplexity();
                }

                foreach ($fileResult['nesting'] as $method => $data) {
                    $nestingByMethod[$method] = array_merge($data, ['file' => $file]);
                }

                foreach ($fileResult['returns'] as $method => $count) {
                    $returnsByMethod[$method] = $count;
                }

                foreach ($fileResult['booleanConditions'] as $method => $conditions) {
                    $booleanConditionsByMethod[$method] = $conditions;
                }
            } catch (ParserException|Error) {
                continue;
            }
        }

        /** @throws ConfigException */
        $config = ConfigResolver::resolveConfig($files, $configPath);
        $engine = QualityEngine::create($config);
        $nestingReturns = new MethodNestingReturns($nestingByMethod, $returnsByMethod);

        $input = new EvaluateInput($qualityMetrics, new MethodMetricsData($nestingReturns, $booleanConditionsByMethod, $complexityByMethod));

        return $engine->evaluate($result, $input);
    }

    /**
     * @psalm-param non-empty-string $file
     *
     * @throws ParserException
     * @throws Error
     *
     * @psalm-return array{metrics: array, complexity: ComplexityCollection}
     */
    private function analyseFile(string $file): array
    {
        $source = file_get_contents($file);

        throw_if($source === false, ParserException::class, 'Cannot read '.$file, 0);

        $lines = substr_count($source, "\n");
        if ($lines === 0 && $source !== '') {
            $lines = 1;
        }

        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        try {
            $nodes = $parser->parse($source);
        } catch (Error $error) {
            throw new ParserException(
                sprintf('Cannot parse %s: ', $file).$error->getMessage(),
                $error->getCode(),
                $error,
            );
        }

        throw_if($nodes === null, ParserException::class, 'Cannot parse '.$file, 0);

        $traverser = new NodeTraverser();
        $complexityVisitor = new EnumAwareComplexityVisitor(false);
        $metricsVisitor = new QualityMetricsVisitor(max(0, $lines));

        $nestingVisitor = new ControlNestingVisitor();
        $returnsVisitor = new ReturnStatementsVisitor();
        $booleanConditionsVisitor = new BooleanConditionsVisitor();

        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($complexityVisitor);
        $traverser->addVisitor($metricsVisitor);
        $traverser->addVisitor($nestingVisitor);
        $traverser->addVisitor($returnsVisitor);
        $traverser->addVisitor($booleanConditionsVisitor);
        $traverser->traverse($nodes);

        $metrics = $metricsVisitor->result();
        $metrics['file_loc'] = $lines;
        $breathing = BreathingAnalyser::analyse($source, $nodes);
        $metrics['breathing'] = $breathing->toArray();

        return [
            'metrics'           => $metrics,
            'complexity'        => $complexityVisitor->result(),
            'nesting'           => $nestingVisitor->result(),
            'returns'           => $returnsVisitor->result(),
            'booleanConditions' => $booleanConditionsVisitor->result(),
        ];
    }
}
