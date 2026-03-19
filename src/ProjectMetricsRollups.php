<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use function explode;

use Illuminate\Support\Collection;
use SebastianBergmann\Complexity\ComplexityCollection;

/**
 * Aggregates complexity statistics for {@see ProjectMetrics}.
 */
final class ProjectMetricsRollups
{
    /**
     * @psalm-return array{count: int, minimum: int, average: float, maximum: int}
     */
    public static function functionStats(ComplexityCollection $complexity): array
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
    public static function methodStats(ComplexityCollection $complexity): array
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
    public static function classStats(ComplexityCollection $complexity): array
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

    public static function totalComplexity(ComplexityCollection $complexity): float
    {
        return (float) collect($complexity)->sum(fn ($item) => $item->cyclomaticComplexity());
    }
}
