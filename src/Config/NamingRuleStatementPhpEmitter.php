<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use function is_array;
use function is_numeric;

use LogicException;

use function sprintf;
use function var_export;

/**
 * @internal
 */
final class NamingRuleStatementPhpEmitter
{
    public static function emit(string $ruleId, mixed $value): string
    {
        if ($ruleId === 'avoid_redundant_naming') {
            if (! is_numeric($value)) {
                throw new LogicException('avoid_redundant_naming expects a number.');
            }

            return sprintf('$config->naming()->avoidRedundantNaming(%s);', var_export((float) $value + 0.0, true));
        }

        if ($ruleId === 'boolean_methods_without_prefix') {
            if (is_array($value)) {
                return sprintf('$config->naming()->importBooleanMethodsRule(%s);', var_export($value, true));
            }

            if (is_numeric($value)) {
                return sprintf('$config->naming()->booleanMethodsWithoutPrefix(%s);', var_export((int) $value, true));
            }

            throw new LogicException('boolean_methods_without_prefix expects array or number.');
        }

        throw new LogicException('Unknown naming rule: '.$ruleId);
    }
}
