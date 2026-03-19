<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

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
            RuleDefinitionPack::tie(new RuleIdentity('max_return_statements', $c), new RulePresentation('Return statements:', 'warning', null), new RuleScoring(4)),

            RuleDefinitionPack::tie(new RuleIdentity('max_boolean_conditions', $c), new RulePresentation('Boolean conditions:', 'warning', null), new RuleScoring(4)),

            RuleDefinitionPack::tie(new RuleIdentity('max_try_catch_blocks', $c), new RulePresentation('Try/catch blocks:', 'warning', null), new RuleScoring(2)),
        ];
    }
}
