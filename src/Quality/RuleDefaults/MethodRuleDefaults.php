<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleDefaults;

use Bunnivo\Soda\Quality\RuleCatalog\RuleCatalog;
use Bunnivo\Soda\Quality\RuleCatalog\RuleDefaultsProvider;

/**
 * @internal
 */
final readonly class MethodRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return RuleCatalog::metadataForRules([
            'max_return_statements',
            'max_boolean_conditions',
            'max_try_catch_blocks',
        ]);
    }
}
