<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleStructure;

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
final class StructuralRuleSurfaceSizeDefinitions
{
    public static function entries(string $sectionKey): array
    {
        $s = $sectionKey;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_file_loc', $s), new RulePresentation('File LOC:', 'warning'), new RuleScoring(700)),

            RuleDefinitionPack::tie(new RuleIdentity('max_properties_per_class', $s), new RulePresentation('Properties per class:', 'warning'), new RuleScoring(5)),
        ];
    }
}
