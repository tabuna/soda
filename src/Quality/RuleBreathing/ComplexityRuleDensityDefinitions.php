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
final class ComplexityRuleDensityDefinitions
{
    public static function entries(string $sectionKey): array
    {
        $c = $sectionKey;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_weighted_cognitive_density', $c), new RulePresentation('Weighted Cognitive Density:', 'warning', 'max'), new RuleScoring(60)),

            RuleDefinitionPack::tie(new RuleIdentity('max_logical_complexity_factor', $c), new RulePresentation('Logical Complexity Factor:', 'warning', 'max'), new RuleScoring(50)),
        ];
    }
}
