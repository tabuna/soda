<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

/**
 * Defines rule sections for soda.json config structure.
 *
 * @internal
 */
final readonly class RuleSections
{
    public const string STRUCTURAL = 'structural';

    public const string COMPLEXITY = 'complexity';

    public const string BREATHING = 'breathing';

    /**
     * @return array<string, list<string>>
     */
    public static function sections(): array
    {
        return [
            self::STRUCTURAL => [
                'max_method_length',
                'max_class_length',
                'max_arguments',
                'max_methods_per_class',
                'max_file_loc',
                'max_properties_per_class',
                'max_public_methods',
                'max_dependencies',
                'max_classes_per_file',
                'max_namespace_depth',
                'max_classes_per_namespace',
                'max_traits_per_class',
                'max_interfaces_per_class',
                'max_classes_per_project',
            ],
            self::COMPLEXITY => [
                'max_cyclomatic_complexity',
                'max_control_nesting',
                'max_weighted_cognitive_density',
                'max_logical_complexity_factor',
                'max_return_statements',
                'max_boolean_conditions',
            ],
            self::BREATHING => [
                'min_code_breathing_score',
                'min_visual_breathing_index',
                'min_identifier_readability_score',
                'min_code_oxygen_level',
            ],
            'naming' => [
                'avoid_redundant_naming',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public const string NAMING = 'naming';

    public static function sectionNames(): array
    {
        return [self::STRUCTURAL, self::COMPLEXITY, self::BREATHING, self::NAMING];
    }

    /**
     * @return array<string, string> Map rule key -> section name
     */
    public static function ruleToSection(): array
    {
        $map = [];

        foreach (self::sections() as $section => $rules) {
            foreach ($rules as $rule) {
                $map[$rule] = $section;
            }
        }

        return $map;
    }
}
