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
        $c = $sectionKey;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_return_statements', $c), new RulePresentation('Return statements:', 'warning'), new RuleScoring(4)),

            RuleDefinitionPack::tie(new RuleIdentity('max_boolean_conditions', $c), new RulePresentation('Boolean conditions:', 'warning'), new RuleScoring(4)),

            RuleDefinitionPack::tie(new RuleIdentity('max_try_catch_blocks', $c), new RulePresentation('Try/catch blocks:', 'warning'), new RuleScoring(2)),
        ];
    }
}
