<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleCatalog;

/**
 * @internal
 */
final class RuleDefinitionPack
{
    public static function tie(
        RuleIdentity $identity,
        RulePresentation $presentation,
        RuleScoring $scoring,
    ): RuleDefinition {
        return new RuleDefinition(new RuleDefinitionFields($identity, $presentation, $scoring));
    }
}
