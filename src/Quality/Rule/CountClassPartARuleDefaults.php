<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 */
final readonly class CountClassPartARuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return RuleSpecBuilder::build([
            RuleSpec::warning('max_properties_per_class', 'Properties per class:'),
            RuleSpec::warning('max_public_methods', 'Public methods:'),
            RuleSpec::warning('max_dependencies', 'Dependencies:'),
            RuleSpec::warning('max_classes_per_file', 'Classes per file:'),
        ]);
    }
}
