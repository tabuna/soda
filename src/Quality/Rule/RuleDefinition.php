<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * Single source fields for one quality rule (default threshold, presentation).
 */
final readonly class RuleDefinition
{
    public function __construct(
        public RuleDefinitionFields $fields,
    ) {}

    /**
     * @return array{severity: 'error'|'warning', label: string, comparison?: 'min'|'max'}
     */
    public function toMetadataEntry(): array
    {
        $presentation = $this->fields->presentation;
        $row = [
            'severity' => $presentation->severity,
            'label'    => $presentation->label,
        ];

        if ($presentation->comparison !== null) {
            $row['comparison'] = $presentation->comparison;
        }

        return $row;
    }
}
