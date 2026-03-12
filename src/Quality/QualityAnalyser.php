<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Analyser;
use Bunnivo\Soda\ParserException;
use Bunnivo\Soda\Quality\Config\ConfigResolver;

use function file_get_contents;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use SebastianBergmann\Complexity\ComplexityCalculatingVisitor;
use SebastianBergmann\Complexity\ComplexityCollection;

use function substr_count;

final class QualityAnalyser
{
    /**
     * @psalm-param list<non-empty-string> $files
     * @psalm-param non-empty-string|null $configPath
     */
    public function analyse(array $files, bool $debug, ?string $configPath = null): QualityResult
    {
        $analyser = new Analyser();
        $result = $analyser->analyse($files, $debug);

        $qualityMetrics = [];
        $complexityByMethod = [];

        foreach ($files as $file) {
            try {
                $fileResult = $this->analyseFile($file);
                $qualityMetrics[$file] = $fileResult['metrics'];

                foreach ($fileResult['complexity']->asArray() as $item) {
                    $complexityByMethod[$item->name()] = $item->cyclomaticComplexity();
                }
            } catch (ParserException|Error) {
                continue;
            }
        }

        $config = ConfigResolver::resolveConfig($files, $configPath);
        $engine = QualityEngine::create($config);

        return $engine->evaluate($result, $qualityMetrics, $complexityByMethod);
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
        if ($source === false) {
            throw new ParserException("Cannot read {$file}", 0);
        }
        $lines = substr_count($source, "\n");
        if ($lines === 0 && $source !== '') {
            $lines = 1;
        }

        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        try {
            $nodes = $parser->parse($source);
        } catch (Error $e) {
            throw new ParserException(
                "Cannot parse {$file}: ".$e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        if ($nodes === null) {
            throw new ParserException("Cannot parse {$file}", 0);
        }

        $traverser = new NodeTraverser();
        $complexityVisitor = new ComplexityCalculatingVisitor(false);
        $metricsVisitor = new QualityMetricsVisitor($lines);

        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($complexityVisitor);
        $traverser->addVisitor($metricsVisitor);

        $traverser->traverse($nodes);

        $metrics = $metricsVisitor->result();
        $metrics['file_loc'] = $lines;

        return [
            'metrics'    => $metrics,
            'complexity' => $complexityVisitor->result(),
        ];
    }
}
