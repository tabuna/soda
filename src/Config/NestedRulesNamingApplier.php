<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use InvalidArgumentException;

use function is_array;
use function is_numeric;

/**
 * @internal
 */
final class NestedRulesNamingApplier
{
    public static function apply(SodaConfig $config, string $ruleId, mixed $value): void
    {
        if ($ruleId === 'avoid_redundant_naming') {
            if (! is_numeric($value)) {
                throw new InvalidArgumentException('avoid_redundant_naming expects a number.');
            }

            $config->naming()->avoidRedundantNaming((float) $value + 0.0);

            return;
        }

        if ($ruleId === 'boolean_methods_without_prefix') {
            self::applyBooleanMethodsWithoutPrefix($config, $value);

            return;
        }

        throw new InvalidArgumentException('Unknown naming rule: '.$ruleId);
    }

    private static function applyBooleanMethodsWithoutPrefix(SodaConfig $config, mixed $value): void
    {
        if (is_array($value)) {
            $config->naming()->importBooleanMethodsRule($value);

            return;
        }

        if (is_numeric($value)) {
            $config->naming()->booleanMethodsWithoutPrefix((int) $value);

            return;
        }

        throw new InvalidArgumentException('boolean_methods_without_prefix expects a number or array.');
    }
}
