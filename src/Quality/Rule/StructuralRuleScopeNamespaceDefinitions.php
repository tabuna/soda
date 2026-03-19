<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 *
 * @return list<RuleDefinition>
 */
final class StructuralRuleScopeNamespaceDefinitions
{
    public static function entries(string $sectionKey): array
    {
        $s = $sectionKey;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_namespace_depth', $s), new RulePresentation('Namespace depth:', 'warning', null), new RuleScoring(4)),

            RuleDefinitionPack::tie(new RuleIdentity('max_classes_per_namespace', $s), new RulePresentation('Classes per namespace:', 'warning', null), new RuleScoring(16)),
        ];
    }
}
