<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use InvalidArgumentException;

use function is_numeric;

/**
 * Complexity + breathing nested rules.
 *
 * @internal
 */
final class NestedRulesMetricsApplier
{
    /**
     * @var array<string, callable(SodaConfig, int): void>|null
     */
    private static ?array $complexityInt = null;

    /**
     * @var array<string, callable(SodaConfig, float): void>|null
     */
    private static ?array $complexityFloat = null;

    /**
     * @var array<string, callable(SodaConfig, float): void>|null
     */
    private static ?array $breathingFloat = null;

    public static function applyComplexity(SodaConfig $config, string $ruleId, mixed $value): void
    {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException(sprintf('Complexity rule "%s" expects a number.', $ruleId));
        }

        $intMap = self::complexityIntAppliers();

        if (isset($intMap[$ruleId])) {
            $intMap[$ruleId]($config, (int) $value);

            return;
        }

        $floatMap = self::complexityFloatAppliers();

        if (isset($floatMap[$ruleId])) {
            $floatMap[$ruleId]($config, (float) $value + 0.0);

            return;
        }

        throw new InvalidArgumentException('Unknown complexity rule: '.$ruleId);
    }

    public static function applyBreathing(SodaConfig $config, string $ruleId, mixed $value): void
    {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException(sprintf('Breathing rule "%s" expects a number.', $ruleId));
        }

        $n = (float) $value + 0.0;
        $map = self::breathingFloatAppliers();

        if (isset($map[$ruleId])) {
            $map[$ruleId]($config, $n);

            return;
        }

        throw new InvalidArgumentException('Unknown breathing rule: '.$ruleId);
    }

    /**
     * @return array<string, callable(SodaConfig, int): void>
     */
    private static function complexityIntAppliers(): array
    {
        return self::$complexityInt ??= [
            'max_cyclomatic_complexity' => static fn (SodaConfig $c, int $n) => $c->complexity()->maxCyclomaticComplexity($n),
            'max_control_nesting'       => static fn (SodaConfig $c, int $n) => $c->complexity()->maxControlNesting($n),
            'max_return_statements'     => static fn (SodaConfig $c, int $n) => $c->complexity()->maxReturnStatements($n),
            'max_boolean_conditions'    => static fn (SodaConfig $c, int $n) => $c->complexity()->maxBooleanConditions($n),
            'max_try_catch_blocks'      => static fn (SodaConfig $c, int $n) => $c->complexity()->maxTryCatchBlocks($n),
        ];
    }

    /**
     * @return array<string, callable(SodaConfig, float): void>
     */
    private static function complexityFloatAppliers(): array
    {
        return self::$complexityFloat ??= [
            'max_weighted_cognitive_density' => static fn (SodaConfig $c, float $n) => $c->complexity()->maxWeightedCognitiveDensity($n),
            'max_logical_complexity_factor'  => static fn (SodaConfig $c, float $n) => $c->complexity()->maxLogicalComplexityFactor($n),
        ];
    }

    /**
     * @return array<string, callable(SodaConfig, float): void>
     */
    private static function breathingFloatAppliers(): array
    {
        return self::$breathingFloat ??= [
            'min_visual_breathing_index'       => static fn (SodaConfig $c, float $n) => $c->breathing()->minVisualBreathingIndex($n),
            'min_code_oxygen_level'            => static fn (SodaConfig $c, float $n) => $c->breathing()->minCodeOxygenLevel($n),
            'min_identifier_readability_score' => static fn (SodaConfig $c, float $n) => $c->breathing()->minIdentifierReadabilityScore($n),
            'min_code_breathing_score'         => static fn (SodaConfig $c, float $n) => $c->breathing()->minCodeBreathingScore($n),
        ];
    }
}
