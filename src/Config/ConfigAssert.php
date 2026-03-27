<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use InvalidArgumentException;

/**
 * @internal
 */
final class ConfigAssert
{
    public static function positiveInt(int $value, string $label): void
    {
        if ($value < 1) {
            throw new InvalidArgumentException(sprintf('%s must be >= 1, got %d.', $label, $value));
        }
    }

    public static function nonNegativeInt(int $value, string $label): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException(sprintf('%s must be >= 0, got %d.', $label, $value));
        }
    }

    public static function nonNegativeNumber(int|float $value, string $label): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException(sprintf('%s must be >= 0.', $label));
        }
    }

    /**
     * @param list<string> $methods
     */
    public static function nonEmptyMethodNames(array $methods, string $label): void
    {
        foreach ($methods as $m) {
            if (! is_string($m) || $m === '') {
                throw new InvalidArgumentException(sprintf('%s: each method name must be a non-empty string.', $label));
            }
        }
    }
}
