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
        return RuleSpecBuilder::build([
            RuleSpec::error('max_method_length', 'Method length:'),
            RuleSpec::error('max_class_length', 'Class length:'),
            RuleSpec::warning('max_arguments', 'Arguments:'),
            RuleSpec::warning('max_methods_per_class', 'Methods per class:'),
            RuleSpec::warning('max_file_loc', 'File LOC:'),
            RuleSpec::error('max_cyclomatic_complexity', 'Cyclomatic complexity:'),
            RuleSpec::error('max_control_nesting', 'Control structure nesting:'),
        ]);
    }
}
