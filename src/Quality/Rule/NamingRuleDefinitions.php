<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

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
            RuleDefinitionPack::tie(new RuleIdentity('avoid_redundant_naming', $n), new RulePresentation('Redundant naming:', 'warning', null), new RuleScoring(80)),
        ];
    }
}
