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
final class BreathingRuleReadabilityDefinitions
{
    public static function entries(string $sectionKey): array
    {
        $b = $sectionKey;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('min_identifier_readability_score', $b), new RulePresentation('Identifier Readability Score:', 'warning', 'min'), new RuleScoring(100)),

            RuleDefinitionPack::tie(new RuleIdentity('min_code_oxygen_level', $b), new RulePresentation('Code Oxygen Level:', 'warning', 'min'), new RuleScoring(100)),
        ];
    }
}
