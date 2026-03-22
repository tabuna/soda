<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleCatalog;

use Bunnivo\Soda\Quality\Report\RuleMetadata;

/**
 * @internal
 *
 * @param 'error'|'warning'|'warning:min'|'warning:max' $mode
 */
final readonly class RuleSpec
{
    public function __construct(
        public string $key,
        public string $label,
        public string $mode = 'warning',
    ) {}

    /**
     * @return array{severity: 'error'|'warning', label: string, comparison?: 'min'|'max'}
     */
    public function toArray(): array
    {
        [$severity, $comparison] = $this->parseMode();
        $a = ['severity' => $severity, 'label' => $this->label];
        if ($comparison !== null) {
            $a['comparison'] = $comparison;
        }

        return $a;
    }

    /**
     * @return array{0: 'error'|'warning', 1: 'min'|'max'|null}
     */
    private function parseMode(): array
    {
        if ($this->mode === 'error') {
            return [RuleMetadata::SEVERITY_ERROR, null];
        }

        if ($this->mode === 'warning:min') {
            return [RuleMetadata::SEVERITY_WARNING, RuleMetadata::COMPARISON_MIN];
        }

        if ($this->mode === 'warning:max') {
            return [RuleMetadata::SEVERITY_WARNING, RuleMetadata::COMPARISON_MAX];
        }

        return [RuleMetadata::SEVERITY_WARNING, null];
    }

    public static function error(string $key, string $label): self
    {
        return new self($key, $label, 'error');
    }

    public static function warning(string $key, string $label): self
    {
        return new self($key, $label, 'warning');
    }

    public static function min(string $key, string $label): self
    {
        return new self($key, $label, 'warning:min');
    }

    public static function max(string $key, string $label): self
    {
        return new self($key, $label, 'warning:max');
    }
}
