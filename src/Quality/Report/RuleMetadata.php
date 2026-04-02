<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Report;

use Bunnivo\Soda\Quality\RuleCatalog\RuleCatalog;

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
        return new self(RuleCatalog::metadataMap());
    }

    /**
     * @return 'error'|'warning'
     */
    public function severity(string $rule): string
    {
        $row = $this->rules[$rule] ?? [];

        return $row['severity'] ?? self::SEVERITY_WARNING;
    }

    public function label(string $rule): string
    {
        $row = $this->rules[$rule] ?? [];

        return $row['label'] ?? 'Unknown:';
    }

    public function comparison(string $rule): string
    {
        $row = $this->rules[$rule] ?? [];

        return $row['comparison'] ?? self::COMPARISON_MAX;
    }

    /**
     * @return list<string>
     */
    public function ruleKeys(): array
    {
        return array_keys($this->rules);
    }
}
