<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 */
final readonly class NamingRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return RuleSpecBuilder::build([
            RuleSpec::warning('avoid_redundant_naming', 'Redundant naming:'),
        ]);
    }
}
