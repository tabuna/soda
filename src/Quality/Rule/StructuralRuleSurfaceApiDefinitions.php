<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 *
 * @return list<RuleDefinition>
 */
final class StructuralRuleSurfaceApiDefinitions
{
    public static function entries(string $sectionKey): array
    {
        $s = $sectionKey;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_public_methods', $s), new RulePresentation('Public methods:', 'warning', null), new RuleScoring(20)),

            RuleDefinitionPack::tie(new RuleIdentity('max_dependencies', $s), new RulePresentation('Dependencies:', 'warning', null), new RuleScoring(8)),
        ];
    }
}
