<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Engine;

use function array_unique;
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

            $directories[$directory]['fileCount']++;
            $type = self::fileType($metrics['classTypes'] ?? []);
            $directories[$directory]['typeCounts'][$type] = ($directories[$directory]['typeCounts'][$type] ?? 0) + 1;
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

        return count($uniqueTypes) === 1 ? $uniqueTypes[0] : self::MIXED_TYPE;
    }
}
