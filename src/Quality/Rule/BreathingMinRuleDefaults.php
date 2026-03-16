<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 */
final readonly class BreathingMinRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return RuleSpecBuilder::build([
            RuleSpec::min('min_code_breathing_score', 'Code Breathing Score:'),
            RuleSpec::min('min_visual_breathing_index', 'Visual Breathing Index:'),
            RuleSpec::min('min_identifier_readability_score', 'Identifier Readability Score:'),
            RuleSpec::min('min_code_oxygen_level', 'Code Oxygen Level:'),
        ]);
    }
}
