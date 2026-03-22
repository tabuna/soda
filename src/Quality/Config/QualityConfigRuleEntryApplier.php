<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

use Bunnivo\Soda\Quality\RuleCatalog\RuleCatalog;

use function is_array;

/**
 * @internal
 */
final class QualityConfigRuleEntryApplier
{
    public static function apply(mixed $key, mixed $value, RuleFlattenScratch $scratch): void
    {
        if (! self::isKnownRuleId($key)) {
            return;
        }

        if (self::isScalarThresholdApplied($key, $value, $scratch)) {
            return;
        }

        if (! is_array($value)) {
            return;
        }

        self::applyStructuredThreshold($key, $value, $scratch);
        self::storeRuleExceptions($key, $value, $scratch);
        self::storeRuleOptions($key, $value, $scratch);
    }

    private static function isKnownRuleId(mixed $key): bool
    {
        return is_string($key) && array_key_exists($key, RuleCatalog::definitions());
    }

    private static function isScalarThresholdApplied(string $key, mixed $value, RuleFlattenScratch $scratch): bool
    {
        if ($value === null) {
            $scratch->disabled[] = $key;

            return true;
        }

        if (! is_numeric($value)) {
            return false;
        }

        $scratch->thresholds[$key] = is_int($value) ? $value : (float) $value;

        return true;
    }

    /**
     * @param array<array-key, mixed> $value
     */
    private static function applyStructuredThreshold(string $key, array $value, RuleFlattenScratch $scratch): void
    {
        if (! array_key_exists('threshold', $value)) {
            return;
        }

        $threshold = $value['threshold'];

        if ($threshold === null) {
            $scratch->disabled[] = $key;

            return;
        }

        if (is_numeric($threshold)) {
            $scratch->thresholds[$key] = is_int($threshold) ? $threshold : (float) $threshold;
        }
    }

    /**
     * @param array<array-key, mixed> $value
     */
    private static function storeRuleExceptions(string $key, array $value, RuleFlattenScratch $scratch): void
    {
        $exceptions = QualityConfigLegacyExceptionMerger::normalizeRuleExceptions($value['exceptions'] ?? null);

        if ($exceptions !== QualityConfigLegacyExceptionMerger::emptyRuleExceptions()) {
            $scratch->exceptions[$key] = $exceptions;
        }
    }

    /**
     * @param array<array-key, mixed> $value
     */
    private static function storeRuleOptions(string $key, array $value, RuleFlattenScratch $scratch): void
    {
        unset($value['threshold'], $value['exceptions']);

        if ($value !== []) {
            $scratch->options[$key] = $value;
        }
    }
}
