<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 *
 * @return list<RuleDefinition>
 */
final class StructuralRuleLengthDefinitions
{
    public static function entries(string $sectionKey): array
    {
        $s = $sectionKey;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_method_length', $s), new RulePresentation('Method length:', 'error', null), new RuleScoring(100)),

            RuleDefinitionPack::tie(new RuleIdentity('max_class_length', $s), new RulePresentation('Class length:', 'error', null), new RuleScoring(500)),

            RuleDefinitionPack::tie(new RuleIdentity('max_arguments', $s), new RulePresentation('Arguments:', 'warning', null), new RuleScoring(3)),

            RuleDefinitionPack::tie(new RuleIdentity('max_methods_per_class', $s), new RulePresentation('Methods per class:', 'warning', null), new RuleScoring(40)),
        ];
    }
}
