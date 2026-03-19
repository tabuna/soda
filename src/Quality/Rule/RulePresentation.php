<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @param 'error'|'warning' $severity
 * @param 'min'|'max'|null  $comparison
 */
final readonly class RulePresentation
{
    public function __construct(
        public string $label,
        public string $severity,
        public ?string $comparison,
    ) {}
}
