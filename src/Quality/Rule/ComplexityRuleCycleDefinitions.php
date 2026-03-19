<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 *
 * @return list<RuleDefinition>
 */
final class ComplexityRuleCycleDefinitions
{
    public static function entries(string $sectionKey): array
    {
        $c = $sectionKey;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_cyclomatic_complexity', $c), new RulePresentation('Cyclomatic complexity:', 'error', null), new RuleScoring(8)),

            RuleDefinitionPack::tie(new RuleIdentity('max_control_nesting', $c), new RulePresentation('Control structure nesting:', 'error', null), new RuleScoring(3)),
        ];
    }
}
