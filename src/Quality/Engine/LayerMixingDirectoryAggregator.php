<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Engine;

use function array_unique;
use function collect;
use function count;
use function dirname;

use Illuminate\Support\Collection;

/**
 * @internal
 */
final class LayerMixingDirectoryAggregator
{
    public const string PLAIN_TYPE = 'Plain';

    private const string MIXED_TYPE = 'Mixed';

    /**
     * @psalm-param array<string, array{classTypes?: array<string, string>}> $qualityMetrics
     *
     * @return Collection<string, array{file: string, fileCount: int, typeCounts: array<string, int>}>
     */
    public static function aggregate(array $qualityMetrics): Collection
    {
        $directories = [];

        foreach ($qualityMetrics as $file => $metrics) {
            $directory = dirname($file);
            $directories[$directory] ??= [
                'file'       => $file,
                'fileCount'  => 0,
                'typeCounts' => [],
            ];

            $row = $directories[$directory];
            $row['fileCount']++;
            $type = self::fileType($metrics['classTypes'] ?? []);
            $counts = $row['typeCounts'];
            $counts[$type] = ($counts[$type] ?? 0) + 1;
            $row['typeCounts'] = $counts;
            $directories[$directory] = $row;
        }

        return collect($directories);
    }

    /**
     * @param array<string, string> $classTypes
     */
    private static function fileType(array $classTypes): string
    {
        if ($classTypes === []) {
            return self::PLAIN_TYPE;
        }

        $uniqueTypes = array_unique(array_values($classTypes));

        return count($uniqueTypes) === 1 ? collect($uniqueTypes)->first() : self::MIXED_TYPE;
    }
}
