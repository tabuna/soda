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
final class ComplexityRuleCycleDefinitions
{
    public static function entries(string $sectionKey): array
    {

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_cyclomatic_complexity', $sectionKey), new RulePresentation('Cyclomatic complexity:', 'error'), new RuleScoring(8)),

            RuleDefinitionPack::tie(new RuleIdentity('max_control_nesting', $sectionKey), new RulePresentation('Control structure nesting:', 'error'), new RuleScoring(3)),
        ];
    }
}
