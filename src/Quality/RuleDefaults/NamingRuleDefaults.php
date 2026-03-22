<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleDefaults;

use Bunnivo\Soda\Quality\RuleCatalog\RuleCatalog;
use Bunnivo\Soda\Quality\RuleCatalog\RuleDefaultsProvider;

/**
 * @internal
 */
final readonly class NamingRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return RuleCatalog::metadataForRules([
            'avoid_redundant_naming',
            'boolean_methods_without_prefix',
        ]);
    }
}
