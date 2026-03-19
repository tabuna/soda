<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 */
final readonly class LengthRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return RuleCatalog::metadataForRules([
            'max_method_length',
            'max_class_length',
            'max_arguments',
            'max_methods_per_class',
            'max_file_loc',
            'max_cyclomatic_complexity',
            'max_control_nesting',
        ]);
    }
}
