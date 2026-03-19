<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 *
 * @return list<RuleDefinition>
 */
final class StructuralRuleSurfaceSizeDefinitions
{
    public static function entries(string $sectionKey): array
    {
        $s = $sectionKey;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_file_loc', $s), new RulePresentation('File LOC:', 'warning', null), new RuleScoring(700)),

            RuleDefinitionPack::tie(new RuleIdentity('max_properties_per_class', $s), new RulePresentation('Properties per class:', 'warning', null), new RuleScoring(5)),
        ];
    }
}
