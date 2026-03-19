<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

/**
 * LOC, complexity, structure and breathing for a set of files (`soda analyse`).
 * For quality rules, use {@see Analyzer}.
 */
final class ProjectMetrics
{
    /**
     * @psalm-param list<non-empty-string> $files
     */
    public function analyse(array $files, bool $debug): Result
    {
        return ProjectMetricsAnalyseResultBuilder::build(
            ProjectMetricsFileGatherer::gather($files, $debug)
        );
    }
}
