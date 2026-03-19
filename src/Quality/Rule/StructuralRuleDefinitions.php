<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 *
 * @return list<RuleDefinition>
 */
final class StructuralRuleDefinitions
{
    public static function all(string $section): array
    {
        return [
            ...StructuralRuleLengthDefinitions::entries($section),

            ...StructuralRuleSurfaceSizeDefinitions::entries($section),

            ...StructuralRuleSurfaceApiDefinitions::entries($section),

            ...StructuralRuleScopeEfferentAndFileDefinitions::entries($section),

            ...StructuralRuleScopeNamespaceDefinitions::entries($section),

            ...StructuralRuleScopeShapeDefinitions::entries($section),
        ];
    }
}
