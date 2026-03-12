<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Bunnivo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda;

use function array_unique;
use function assert;
use function count;
use function dirname;
use function explode;
use function file_get_contents;
use function is_string;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use SebastianBergmann\Complexity\ComplexityCalculatingVisitor;
use SebastianBergmann\Complexity\ComplexityCollection;
use SebastianBergmann\LinesOfCode\LineCountingVisitor;
use SebastianBergmann\LinesOfCode\LinesOfCode;

use function sprintf;
use function substr_count;

final class Analyser
{
    /**
     * @psalm-param list<non-empty-string> $files
     */
    public function analyse(array $files, bool $debug): Result
    {
        [$errors, $directories, $complexity, $linesOfCode, $structureResults] = $this->collectFileMetrics($files, $debug);
        $functionStats = $this->computeFunctionStats($complexity);
        $methodStats = $this->computeMethodStats($complexity);
        $classStats = $this->computeClassStats($complexity);
        $lloc = $linesOfCode->logicalLinesOfCode();
        $mergedStructure = Structure\MetricsMerger::merge($structureResults);
        $structureStats = Structure\StatsCalculator::compute($mergedStructure, $lloc);

        $loc = new LocMetrics([
            'directories'           => count(array_unique($directories)),
            'files'                 => count($files),
            'linesOfCode'           => $linesOfCode->linesOfCode(),
            'commentLinesOfCode'    => $linesOfCode->commentLinesOfCode(),
            'nonCommentLinesOfCode' => $linesOfCode->nonCommentLinesOfCode(),
            'logicalLinesOfCode'    => $lloc,
        ]);

        $structure = new Structure\Metrics(array_merge($mergedStructure, $structureStats));

        $totalComplexity = $this->totalComplexity($complexity);
        $complexityMetrics = new ComplexityMetrics([
            'functions'       => $functionStats['count'],
            'funcLowest'      => $functionStats['minimum'],
            'funcAverage'     => $functionStats['average'],
            'funcHighest'     => $functionStats['maximum'],
            'classesOrTraits' => $methodStats['classesOrTraits'],
            'methods'         => $methodStats['count'],
            'methodLowest'    => $methodStats['minimum'],
            'methodAverage'   => $methodStats['average'],
            'methodHighest'   => $methodStats['maximum'],
            'classLowest'     => $classStats['minimum'],
            'classAverage'    => $classStats['average'],
            'classHighest'    => $classStats['maximum'],
            'averagePerLloc'  => $lloc > 0 ? $totalComplexity / $lloc : 0.0,
        ]);

        return new Result($errors, $loc, $complexityMetrics, $structure);
    }

    /**
     * @psalm-param list<non-empty-string> $files
     *
     * @psalm-return array{0: list<non-empty-string>, 1: list<string>, 2: ComplexityCollection, 3: LinesOfCode, 4: list<array<string, mixed>>}
     */
    private function collectFileMetrics(array $files, bool $debug): array
    {
        $errors = [];
        $directories = [];
        $complexity = ComplexityCollection::fromList();
        /** @psalm-suppress MissingThrowsDocblock */
        $linesOfCode = new LinesOfCode(0, 0, 0, 0);
        $structureResults = [];

        foreach ($files as $file) {
            if ($debug) {
                echo $file.PHP_EOL;
            }
            $directories[] = dirname($file);

            try {
                $result = $this->analyseFile($file);
                $complexity = $complexity->mergeWith($result['complexity']);
                $linesOfCode = $linesOfCode->plus($result['linesOfCode']);
                $structureResults[] = $result['structure'];
            } catch (ParserException $e) {
                $message = $e->getMessage();
                assert(is_string($message) && ! empty($message));
                $errors[] = $message;
            }
        }

        return [$errors, $directories, $complexity, $linesOfCode, $structureResults];
    }

    /**
     * @psalm-return array{count: int, minimum: int, average: float, maximum: int}
     */
    private function computeFunctionStats(ComplexityCollection $complexity): array
    {
        $items = $complexity->isFunction();
        $stats = ComplexityStatistics::from($items);

        return [
            'count'   => $items->count(),
            'minimum' => $stats['minimum'],
            'average' => $stats['average'],
            'maximum' => $stats['maximum'],
        ];
    }

    /**
     * @psalm-return array{classesOrTraits: int, count: int, minimum: int, average: float, maximum: int}
     */
    private function computeMethodStats(ComplexityCollection $complexity): array
    {
        $items = $complexity->isMethod();
        $classesOrTraits = [];
        foreach ($items as $item) {
            $classesOrTraits[] = explode('::', $item->name())[0];
        }
        $classesOrTraits = count(array_unique($classesOrTraits));
        $stats = ComplexityStatistics::from($items);

        return [
            'classesOrTraits' => $classesOrTraits,
            'count'           => $items->count(),
            'minimum'         => $stats['minimum'],
            'average'         => $stats['average'],
            'maximum'         => $stats['maximum'],
        ];
    }

    /**
     * @psalm-return array{minimum: float, average: float, maximum: float}
     */
    private function computeClassStats(ComplexityCollection $complexity): array
    {
        $items = $complexity->isMethod();
        $classes = [];
        foreach ($items as $item) {
            $class = explode('::', $item->name())[0];
            $classes[$class] = ($classes[$class] ?? 0) + $item->cyclomaticComplexity();
        }
        $values = array_values($classes);

        return [
            'minimum' => ! empty($values) ? (float) min($values) : 0.0,
            'average' => ! empty($values) ? array_sum($values) / count($values) : 0.0,
            'maximum' => ! empty($values) ? (float) max($values) : 0.0,
        ];
    }

    private function totalComplexity(ComplexityCollection $complexity): float
    {
        $sum = 0;
        foreach ($complexity as $item) {
            $sum += $item->cyclomaticComplexity();
        }

        return (float) $sum;
    }

    /**
     * @psalm-param non-empty-string $file
     *
     * @throws ParserException
     *
     * @psalm-return array{complexity: ComplexityCollection, linesOfCode: LinesOfCode, structure: array<string, mixed>}
     */
    private function analyseFile(string $file): array
    {
        $parser = $this->parser();
        $source = file_get_contents($file);
        $lines = substr_count($source, "\n");

        if ($lines === 0 && ! empty($source)) {
            $lines = 1;
        }

        assert($lines >= 0);

        try {
            $nodes = $parser->parse($source);

            assert($nodes !== null);

            $traverser = new NodeTraverser;

            $complexityCalculatingVisitor = new ComplexityCalculatingVisitor(false);
            $lineCountingVisitor = new LineCountingVisitor($lines);
            $structureVisitor = new Structure\MetricsVisitor();

            $traverser->addVisitor(new NameResolver);
            $traverser->addVisitor(new ParentConnectingVisitor);
            $traverser->addVisitor($complexityCalculatingVisitor);
            $traverser->addVisitor($lineCountingVisitor);
            $traverser->addVisitor($structureVisitor);

            $traverser->traverse($nodes);
        } catch (Error $error) {
            throw new ParserException(
                sprintf(
                    'Cannot parse %s: %s',
                    $file,
                    $error->getMessage(),
                ),
                $error->getCode(),
                $error,
            );
        }

        return [
            'complexity'  => $complexityCalculatingVisitor->result(),
            'linesOfCode' => $lineCountingVisitor->result(),
            'structure'   => $structureVisitor->result(),
        ];
    }

    private function parser(): Parser
    {
        return (new ParserFactory())->createForNewestSupportedVersion();
    }
}
