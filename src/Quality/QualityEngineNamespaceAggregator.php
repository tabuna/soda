<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Illuminate\Support\Collection;

/**
 * @internal
 */
final class QualityEngineNamespaceAggregator
{
    /**
     * @psalm-param array<string, array<string, mixed>> $qualityMetrics
     *
     * @return Collection<string, array{count: int, file: string}>
     */
    public static function aggregate(array $qualityMetrics): Collection
    {
        /** @var array<string, int> $empty */
        $empty = [];

        return collect($qualityMetrics)
            ->flatMap(function (array $data, string $file) use ($empty): iterable {
                $namespaces = $data['namespaces'] ?? $empty;

                return collect($namespaces)->map(
                    fn (int $count, string $namespace): array => [
                        'ns'    => $namespace,
                        'count' => $count,
                        'file'  => $file,
                    ],
                );
            })
            ->groupBy('ns')
            ->map(function (Collection $group): array {
                $first = $group->first();

                return [
                    'count' => $group->sum('count'),
                    'file'  => is_array($first) && isset($first['file']) ? $first['file'] : '',
                ];
            });
    }
}
