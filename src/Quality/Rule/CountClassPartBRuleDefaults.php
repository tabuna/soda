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
        return RuleSpecBuilder::build([
            RuleSpec::warning('max_namespace_depth', 'Namespace depth:'),
            RuleSpec::warning('max_classes_per_namespace', 'Classes per namespace:'),
            RuleSpec::warning('max_traits_per_class', 'Traits per class:'),
            RuleSpec::warning('max_interfaces_per_class', 'Interfaces per class:'),
        ]);
    }
}
