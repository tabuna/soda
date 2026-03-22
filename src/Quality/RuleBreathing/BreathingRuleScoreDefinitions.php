<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleBreathing;

use Bunnivo\Soda\Quality\RuleCatalog\RuleDefinition;
use Bunnivo\Soda\Quality\RuleCatalog\RuleDefinitionPack;
use Bunnivo\Soda\Quality\RuleCatalog\RuleIdentity;
use Bunnivo\Soda\Quality\RuleCatalog\RulePresentation;
use Bunnivo\Soda\Quality\RuleCatalog\RuleScoring;

/**
 * @internal
 *
 * @return list<RuleDefinition>
 */
final class BreathingRuleScoreDefinitions
{
    public static function entries(string $sectionKey): array
    {
        $b = $sectionKey;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('min_code_breathing_score', $b), new RulePresentation('Code Breathing Score:', 'warning', 'min'), new RuleScoring(100)),

            RuleDefinitionPack::tie(new RuleIdentity('min_visual_breathing_index', $b), new RulePresentation('Visual Breathing Index:', 'warning', 'min'), new RuleScoring(70)),
        ];
    }
}
