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
        return RuleCatalog::metadataForRules([
            'max_properties_per_class',
            'max_public_methods',
            'max_dependencies',
            'max_efferent_coupling',
            'max_classes_per_file',
        ]);
    }
}
