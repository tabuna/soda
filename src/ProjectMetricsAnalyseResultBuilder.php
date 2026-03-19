<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Breathing\BreathingMetrics;

use function count;

use SebastianBergmann\Complexity\ComplexityCollection;
use SebastianBergmann\LinesOfCode\LinesOfCode;

/**
 * @internal
 */
final class ProjectMetricsAnalyseResultBuilder
{
    /**
     * @psalm-param array{
     *     files: list<non-empty-string>,
     *     errors: list<non-empty-string>,
     *     dirs: list<string>,
     *     complexity: ComplexityCollection,
     *     loc: LinesOfCode,
     *     structureResults: list<array<string, mixed>>,
     *     breathingList: list<BreathingMetrics>
     * } $gathered
     */
    public static function build(array $gathered): Result
    {
        $files = $gathered['files'];

        $errors = $gathered['errors'];

        $dirs = $gathered['dirs'];

        $complexity = $gathered['complexity'];

        $loc = $gathered['loc'];

        $structureResults = $gathered['structureResults'];

        $breathingList = $gathered['breathingList'];

        $functionStats = ProjectMetricsRollups::functionStats($complexity);
        $methodStats = ProjectMetricsRollups::methodStats($complexity);
        $classStats = ProjectMetricsRollups::classStats($complexity);
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

        $totalComplexity = ProjectMetricsRollups::totalComplexity($complexity);
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

        $breathing = ProjectMetricsBreathingAggregator::aggregate($breathingList);
        $core = new CoreMetrics($locMetrics, $complexityMetrics);
        $extended = new ExtendedMetrics($structure, $breathing);

        return new Result($errors, $core, $extended);
    }
}
