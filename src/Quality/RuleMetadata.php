<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

/**
 * Presentation metadata for quality rules (severity, display label).
 */
final readonly class RuleMetadata
{
    public const string SEVERITY_ERROR = 'error';
    public const string SEVERITY_WARNING = 'warning';

    public const string COMPARISON_MAX = 'max';
    public const string COMPARISON_MIN = 'min';

    /**
     * @psalm-param array<string, array{severity: 'error'|'warning', label: string, comparison?: 'min'|'max'}> $rules
     */
    public function __construct(
        private array $rules,
    ) {}

    public static function default(): self
    {
        return new self(self::defaults());
    }

    /**
     * @return 'error'|'warning'
     */
    public function severity(string $rule): string
    {
        return $this->rules[$rule]['severity'] ?? self::SEVERITY_WARNING;
    }

    public function label(string $rule): string
    {
        return $this->rules[$rule]['label'] ?? 'Unknown:';
    }

    public function comparison(string $rule): string
    {
        return $this->rules[$rule]['comparison'] ?? self::COMPARISON_MAX;
    }

    /**
     * @return array<string, array{severity: 'error'|'warning', label: string, comparison?: 'min'|'max'}>
     */
    public static function defaults(): array
    {
        return [
            'max_method_length'                     => ['severity' => self::SEVERITY_ERROR, 'label' => 'Method length:'],
            'max_class_length'                      => ['severity' => self::SEVERITY_ERROR, 'label' => 'Class length:'],
            'max_arguments'                         => ['severity' => self::SEVERITY_WARNING, 'label' => 'Arguments:'],
            'max_methods_per_class'                 => ['severity' => self::SEVERITY_WARNING, 'label' => 'Methods per class:'],
            'max_file_loc'                          => ['severity' => self::SEVERITY_WARNING, 'label' => 'File LOC:'],
            'max_cyclomatic_complexity'             => ['severity' => self::SEVERITY_ERROR, 'label' => 'Cyclomatic complexity:'],
            'max_properties_per_class'              => ['severity' => self::SEVERITY_WARNING, 'label' => 'Properties per class:'],
            'max_public_methods'                    => ['severity' => self::SEVERITY_WARNING, 'label' => 'Public methods:'],
            'max_dependencies'                      => ['severity' => self::SEVERITY_WARNING, 'label' => 'Dependencies:'],
            'max_classes_per_file'                  => ['severity' => self::SEVERITY_WARNING, 'label' => 'Classes per file:'],
            'max_namespace_depth'                   => ['severity' => self::SEVERITY_WARNING, 'label' => 'Namespace depth:'],
            'max_classes_per_namespace'             => ['severity' => self::SEVERITY_WARNING, 'label' => 'Classes per namespace:'],
            'max_traits_per_class'                  => ['severity' => self::SEVERITY_WARNING, 'label' => 'Traits per class:'],
            'max_interfaces_per_class'              => ['severity' => self::SEVERITY_WARNING, 'label' => 'Interfaces per class:'],
            'max_classes_per_project'               => ['severity' => self::SEVERITY_ERROR, 'label' => 'Classes per project:'],
            'min_code_breathing_score'              => ['severity' => self::SEVERITY_WARNING, 'label' => 'Code Breathing Score:', 'comparison' => self::COMPARISON_MIN],
            'min_visual_breathing_index'            => ['severity' => self::SEVERITY_WARNING, 'label' => 'Visual Breathing Index:', 'comparison' => self::COMPARISON_MIN],
            'min_identifier_readability_score'      => ['severity' => self::SEVERITY_WARNING, 'label' => 'Identifier Readability Score:', 'comparison' => self::COMPARISON_MIN],
            'min_code_oxygen_level'                 => ['severity' => self::SEVERITY_WARNING, 'label' => 'Code Oxygen Level:', 'comparison' => self::COMPARISON_MIN],
            'max_weighted_cognitive_density'        => ['severity' => self::SEVERITY_WARNING, 'label' => 'Weighted Cognitive Density:', 'comparison' => self::COMPARISON_MAX],
            'max_logical_complexity_factor'         => ['severity' => self::SEVERITY_WARNING, 'label' => 'Logical Complexity Factor:', 'comparison' => self::COMPARISON_MAX],
        ];
    }
}
