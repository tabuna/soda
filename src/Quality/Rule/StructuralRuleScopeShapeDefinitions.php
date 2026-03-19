<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 *
 * @return list<RuleDefinition>
 */
final class StructuralRuleScopeShapeDefinitions
{
    public static function entries(string $sectionKey): array
    {
        $s = $sectionKey;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_traits_per_class', $s), new RulePresentation('Traits per class:', 'warning', null), new RuleScoring(10)),

            RuleDefinitionPack::tie(new RuleIdentity('max_interfaces_per_class', $s), new RulePresentation('Interfaces per class:', 'warning', null), new RuleScoring(5)),

            RuleDefinitionPack::tie(new RuleIdentity('max_classes_per_project', $s), new RulePresentation('Classes per project:', 'error', null), new RuleScoring(300)),
        ];
    }
}
