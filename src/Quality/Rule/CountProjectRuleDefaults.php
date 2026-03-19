<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 */
final readonly class CountProjectRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return RuleCatalog::metadataForRules([
            'max_classes_per_project',
        ]);
    }
}
