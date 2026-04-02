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
final class StructuralRuleLengthDefinitions
{
    public static function entries(string $sectionKey): array
    {

        return [
            RuleDefinitionPack::tie(new RuleIdentity('max_method_length', $sectionKey), new RulePresentation('Method length:', 'error'), new RuleScoring(100)),

            RuleDefinitionPack::tie(new RuleIdentity('max_class_length', $sectionKey), new RulePresentation('Class length:', 'error'), new RuleScoring(500)),

            RuleDefinitionPack::tie(new RuleIdentity('max_arguments', $sectionKey), new RulePresentation('Arguments:', 'warning'), new RuleScoring(3)),

            RuleDefinitionPack::tie(new RuleIdentity('max_methods_per_class', $sectionKey), new RulePresentation('Methods per class:', 'warning'), new RuleScoring(40)),
        ];
    }
}
