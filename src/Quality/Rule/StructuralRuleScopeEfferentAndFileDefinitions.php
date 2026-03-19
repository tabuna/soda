<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 *
 * @return list<RuleDefinition>
 */
final class StructuralRuleScopeEfferentAndFileDefinitions
{
    public static function entries(string $sectionKey): array
    {
        $s = $sectionKey;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_efferent_coupling', $s), new RulePresentation('Efferent coupling (Ce):', 'warning', null), new RuleScoring(10)),

            RuleDefinitionPack::tie(new RuleIdentity('max_classes_per_file', $s), new RulePresentation('Classes per file:', 'warning', null), new RuleScoring(1)),
        ];
    }
}
