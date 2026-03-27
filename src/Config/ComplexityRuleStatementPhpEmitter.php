<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use function is_numeric;

use LogicException;

use function sprintf;
use function var_export;

/**
 * @internal
 */
final class ComplexityRuleStatementPhpEmitter
{
    /** @var array<string, string> */
    private const array INT_METHODS = [
        'max_cyclomatic_complexity' => 'maxCyclomaticComplexity',
        'max_control_nesting'       => 'maxControlNesting',
        'max_return_statements'     => 'maxReturnStatements',
        'max_boolean_conditions'    => 'maxBooleanConditions',
        'max_try_catch_blocks'      => 'maxTryCatchBlocks',
    ];

    /** @var array<string, string> */
    private const array FLOAT_METHODS = [
        'max_weighted_cognitive_density' => 'maxWeightedCognitiveDensity',
        'max_logical_complexity_factor'  => 'maxLogicalComplexityFactor',
    ];

    public static function emit(string $ruleId, mixed $value): string
    {
        if (! is_numeric($value)) {
            throw new LogicException(sprintf('Complexity rule "%s" expects a number.', $ruleId));
        }

        $intM = self::INT_METHODS[$ruleId] ?? null;

        if ($intM !== null) {
            return sprintf('$config->complexity()->%s(%s);', $intM, var_export((int) $value, true));
        }

        $floatM = self::FLOAT_METHODS[$ruleId] ?? null;

        if ($floatM !== null) {
            return sprintf('$config->complexity()->%s(%s);', $floatM, var_export((float) $value + 0.0, true));
        }

        throw new LogicException('Unknown complexity rule: '.$ruleId);
    }
}
