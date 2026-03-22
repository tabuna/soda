<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

/**
 * @internal
 */
final class QualityConfigThresholdFilter
{
    /**
     * @param array<string, int|float> $flattened
     *
     * @return array<string, int|float>
     */
    public static function validThresholds(array $flattened): array
    {
        return collect($flattened)->filter(static function (mixed $value): bool {
            return is_numeric($value) && (float) $value >= 0;
        })->map(static fn (mixed $value): int|float => is_int($value) ? $value : $value)->all();
    }
}
