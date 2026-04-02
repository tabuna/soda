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
final class ComplexityRuleFlowDefinitions
{
    public static function entries(string $sectionKey): array
    {

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_return_statements', $sectionKey), new RulePresentation('Return statements:', 'warning'), new RuleScoring(4)),

            RuleDefinitionPack::tie(new RuleIdentity('max_boolean_conditions', $sectionKey), new RulePresentation('Boolean conditions:', 'warning'), new RuleScoring(4)),

            RuleDefinitionPack::tie(new RuleIdentity('max_try_catch_blocks', $sectionKey), new RulePresentation('Try/catch blocks:', 'warning'), new RuleScoring(2)),
        ];
    }
}
