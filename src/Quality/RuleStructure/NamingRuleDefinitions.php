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
final class NamingRuleDefinitions
{
    public static function all(string $section): array
    {
        $n = $section;

        return [
            RuleDefinitionPack::tie(new RuleIdentity('avoid_redundant_naming', $n), new RulePresentation('Redundant naming:', 'warning'), new RuleScoring(80)),

            RuleDefinitionPack::tie(new RuleIdentity('boolean_methods_without_prefix', $n), new RulePresentation('Boolean method prefixes:', 'warning'), new RuleScoring(0)),
        ];
    }
}
