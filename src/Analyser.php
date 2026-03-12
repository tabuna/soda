<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use function assert;

use Bunnivo\Soda\Breathing\AirinessFactors;
use Bunnivo\Soda\Breathing\BreathingFactors;
use Bunnivo\Soda\Breathing\BreathingMetrics;
use Bunnivo\Soda\Breathing\CognitiveLoad;

use function count;
use function dirname;
use function explode;

use Illuminate\Support\Collection;

use function is_string;

use SebastianBergmann\Complexity\ComplexityCollection;
use SebastianBergmann\LinesOfCode\LinesOfCode;

final class Analyser
{
    /**
     * @psalm-param list<non-empty-string> $files
     */
    public function analyse(array $files, bool $debug): Result
    {
        [$errors, $dirs, $complexity, $loc, $structureResults, $breathingList] = $this->collectFileMetrics($files, $debug);
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
            'averagePerLloc'  => $lloc > 0 ? $totalComplexity / (float) $lloc : 0.0,
        ]);

        $breathing = $this->aggregateBreathing($breathingList);
        $core = new CoreMetrics($locMetrics, $complexityMetrics);
        $extended = new ExtendedMetrics($structure, $breathing);

        return new Result($errors, $core, $extended);
    }

    /**
     * @psalm-param list<non-empty-string> $files
     *
     * @psalm-return array{0: list<non-empty-string>, 1: list<string>, 2: ComplexityCollection, 3: LinesOfCode, 4: list<array<string, mixed>>, 5: list<BreathingMetrics>}
     */
    private function collectFileMetrics(array $files, bool $debug): array
    {
        $errors = [];
        $dirs = [];
        $complexity = ComplexityCollection::fromList();
        /** @psalm-suppress MissingThrowsDocblock */
        $loc = new LinesOfCode(0, 0, 0, 0);
        $structureResults = [];
        $breathingList = [];

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
                $breathingList[] = $result['breathing'];
            } catch (ParserException $e) {
                $message = $e->getMessage();
                assert(is_string($message) && ! empty($message));
                $errors[] = $message;
            }
        }

        return [$errors, $dirs, $complexity, $loc, $structureResults, $breathingList];
    }

    /**
     * @param list<BreathingMetrics> $list
     */
    private function aggregateBreathing(array $list): ?BreathingMetrics
    {
        if ($list === []) {
            return null;
        }

        $wcd = collect($list)->avg(fn (BreathingMetrics $m) => $m->wcd()) ?? 0.0;
        $lcf = collect($list)->avg(fn (BreathingMetrics $m) => $m->lcf()) ?? 0.0;
        $vbi = collect($list)->avg(fn (BreathingMetrics $m) => $m->vbi()) ?? 0.0;
        $irs = collect($list)->avg(fn (BreathingMetrics $m) => $m->irs()) ?? 0.0;
        $col = collect($list)->avg(fn (BreathingMetrics $m) => $m->col()) ?? 0.0;
        $cbs = collect($list)->avg(fn (BreathingMetrics $m) => $m->cbs()) ?? 0.0;

        $factors = new BreathingFactors(
            new CognitiveLoad($wcd, $lcf),
            new AirinessFactors($vbi, $irs, $col),
        );

        return BreathingMetrics::fromFactors($factors, $cbs);
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
            'minimum' => (float) ($values->min() ?? 0.0),
            'average' => (float) ($values->avg() ?? 0.0),
            'maximum' => (float) ($values->max() ?? 0.0),
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
     * @psalm-return array{complexity: ComplexityCollection, linesOfCode: LinesOfCode, structure: array<string, mixed>, breathing: BreathingMetrics}
     */
    private function analyseFile(string $file): array
    {
        return (new FileAnalyser())->analyse($file);
    }
}
