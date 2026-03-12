<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use function assert;
use function count;
use function dirname;
use function explode;
use function file_get_contents;

use Illuminate\Support\Collection;

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
        [$errors, $dirs, $complexity, $loc, $structureResults] = $this->collectFileMetrics($files, $debug);
        $functionStats = $this->computeFunctionStats($complexity);
        $methodStats = $this->computeMethodStats($complexity);
        $classStats = $this->computeClassStats($complexity);
        $lloc = $loc->logicalLinesOfCode();
        $merged = Structure\MetricsMerger::merge($structureResults);
        $structureStats = Structure\StatsCalculator::compute($merged, $lloc);

        $locMetrics = new LocMetrics([
            'directories'           => collect($dirs)->unique()->count(),
            'files'                 => count($files),
            'linesOfCode'           => $loc->linesOfCode(),
            'commentLinesOfCode'    => $loc->commentLinesOfCode(),
            'nonCommentLinesOfCode' => $loc->nonCommentLinesOfCode(),
            'logicalLinesOfCode'    => $lloc,
        ]);

        $structure = new Structure\Metrics(array_merge($merged, $structureStats));

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

        return new Result($errors, $locMetrics, $complexityMetrics, $structure);
    }

    /**
     * @psalm-param list<non-empty-string> $files
     *
     * @psalm-return array{0: list<non-empty-string>, 1: list<string>, 2: ComplexityCollection, 3: LinesOfCode, 4: list<array<string, mixed>>}
     */
    private function collectFileMetrics(array $files, bool $debug): array
    {
        $errors = [];
        $dirs = [];
        $complexity = ComplexityCollection::fromList();
        /** @psalm-suppress MissingThrowsDocblock */
        $loc = new LinesOfCode(0, 0, 0, 0);
        $structureResults = [];

        foreach ($files as $file) {
            if ($debug) {
                echo $file.PHP_EOL;
            }
            $dirs[] = dirname($file);

            try {
                $result = $this->analyseFile($file);
                $complexity = $complexity->mergeWith($result['complexity']);
                $loc = $loc->plus($result['linesOfCode']);
                $structureResults[] = $result['structure'];
            } catch (ParserException $e) {
                $message = $e->getMessage();
                assert(is_string($message) && ! empty($message));
                $errors[] = $message;
            }
        }

        return [$errors, $dirs, $complexity, $loc, $structureResults];
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
        $classesOrTraits = collect($items)
            ->map(fn ($item) => explode('::', $item->name())[0])
            ->unique()
            ->count();
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
        $values = collect($complexity->isMethod())
            ->groupBy(fn ($item) => explode('::', $item->name())[0])
            ->map(fn (Collection $group) => $group->sum(fn ($item) => $item->cyclomaticComplexity()))
            ->values();

        return [
            'minimum' => $values->min() ?? 0.0,
            'average' => $values->avg() ?? 0.0,
            'maximum' => $values->max() ?? 0.0,
        ];
    }

    private function totalComplexity(ComplexityCollection $complexity): float
    {
        return (float) collect($complexity)->sum(fn ($item) => $item->cyclomaticComplexity());
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
