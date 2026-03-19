<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Breathing\AirinessFactors;
use Bunnivo\Soda\Breathing\BreathingFactors;
use Bunnivo\Soda\Breathing\BreathingMetrics;
use Bunnivo\Soda\Breathing\CognitiveLoad;

/**
 * Averages per-file breathing metrics for {@see ProjectMetrics}.
 */
final class ProjectMetricsBreathingAggregator
{
    /**
     * @param list<BreathingMetrics> $list
     */
    public static function aggregate(array $list): ?BreathingMetrics
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
}
