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
        return RuleCatalog::metadataForRules([
            'max_weighted_cognitive_density',
            'max_logical_complexity_factor',
        ]);
    }
}
