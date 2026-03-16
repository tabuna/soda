<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 */
final readonly class BreathingMaxRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return RuleSpecBuilder::build([
            RuleSpec::max('max_weighted_cognitive_density', 'Weighted Cognitive Density:'),
            RuleSpec::max('max_logical_complexity_factor', 'Logical Complexity Factor:'),
        ]);
    }
}
