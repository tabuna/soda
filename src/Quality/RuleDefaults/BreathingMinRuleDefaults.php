<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleDefaults;

use Bunnivo\Soda\Quality\RuleCatalog\RuleCatalog;
use Bunnivo\Soda\Quality\RuleCatalog\RuleDefaultsProvider;

/**
 * @internal
 */
final readonly class BreathingMinRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return RuleCatalog::metadataForRules([
            'min_code_breathing_score',
            'min_visual_breathing_index',
            'min_identifier_readability_score',
            'min_code_oxygen_level',
        ]);
    }
}
