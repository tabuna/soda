<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 */
final readonly class NamingRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return RuleCatalog::metadataForRules([
            'avoid_redundant_naming',
        ]);
    }
}
