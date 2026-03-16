<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 */
final readonly class MethodRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return RuleSpecBuilder::build([
            RuleSpec::warning('max_return_statements', 'Return statements:'),
            RuleSpec::warning('max_boolean_conditions', 'Boolean conditions:'),
        ]);
    }
}
