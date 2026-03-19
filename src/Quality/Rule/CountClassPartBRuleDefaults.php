<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 */
final readonly class CountClassPartBRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return RuleCatalog::metadataForRules([
            'max_namespace_depth',
            'max_classes_per_namespace',
            'max_traits_per_class',
            'max_interfaces_per_class',
        ]);
    }
}
