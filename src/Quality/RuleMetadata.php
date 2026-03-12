<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Bunnivo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda\Quality;

/**
 * Presentation metadata for quality rules (severity, display label).
 */
final readonly class RuleMetadata
{
    public const string SEVERITY_ERROR = 'error';
    public const string SEVERITY_WARNING = 'warning';

    /**
     * @psalm-param array<string, array{severity: 'error'|'warning', label: string}> $rules
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

    /**
     * @return array<string, array{severity: 'error'|'warning', label: string}>
     */
    public static function defaults(): array
    {
        return [
            'max_method_length'          => ['severity' => self::SEVERITY_ERROR, 'label' => 'Method length:'],
            'max_class_length'           => ['severity' => self::SEVERITY_ERROR, 'label' => 'Class length:'],
            'max_arguments'              => ['severity' => self::SEVERITY_WARNING, 'label' => 'Arguments:'],
            'max_methods_per_class'      => ['severity' => self::SEVERITY_WARNING, 'label' => 'Methods per class:'],
            'max_file_loc'               => ['severity' => self::SEVERITY_WARNING, 'label' => 'File LOC:'],
            'max_cyclomatic_complexity'  => ['severity' => self::SEVERITY_ERROR, 'label' => 'Cyclomatic complexity:'],
            'max_properties_per_class'   => ['severity' => self::SEVERITY_WARNING, 'label' => 'Properties per class:'],
            'max_public_methods'         => ['severity' => self::SEVERITY_WARNING, 'label' => 'Public methods:'],
            'max_dependencies'           => ['severity' => self::SEVERITY_WARNING, 'label' => 'Dependencies:'],
            'max_classes_per_file'       => ['severity' => self::SEVERITY_WARNING, 'label' => 'Classes per file:'],
            'max_namespace_depth'        => ['severity' => self::SEVERITY_WARNING, 'label' => 'Namespace depth:'],
            'max_classes_per_namespace'  => ['severity' => self::SEVERITY_WARNING, 'label' => 'Classes per namespace:'],
            'max_traits_per_class'       => ['severity' => self::SEVERITY_WARNING, 'label' => 'Traits per class:'],
            'max_interfaces_per_class'   => ['severity' => self::SEVERITY_WARNING, 'label' => 'Interfaces per class:'],
            'max_classes_per_project'    => ['severity' => self::SEVERITY_ERROR, 'label' => 'Classes per project:'],
        ];
    }
}
