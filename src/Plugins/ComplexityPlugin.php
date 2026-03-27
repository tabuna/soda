<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Plugins;

use Bunnivo\Soda\Config\SodaPlugin;
use Bunnivo\Soda\Quality\Rule\MethodRules;

/**
 * Method-level complexity rules: cyclomatic complexity, nesting, LOC, arguments,
 * boolean conditions, return statements, try/catch blocks.
 *
 * Covers: max_cyclomatic_complexity, max_control_nesting, max_method_length,
 *         max_arguments, max_boolean_conditions, max_return_statements, max_try_catch_blocks,
 *         max_weighted_cognitive_density, max_logical_complexity_factor.
 */
final class ComplexityPlugin implements SodaPlugin
{
    #[\Override]
    public function checkers(): array
    {
        return [
            new MethodRules,
        ];
    }
}
