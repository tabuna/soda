<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\Rule\BreathingRuleDefaults;
use Bunnivo\Soda\Quality\Rule\MethodRuleDefaults;
use Bunnivo\Soda\Quality\Rule\NamingRuleDefaults;
use Bunnivo\Soda\Quality\Rule\StructureRuleDefaults;

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
        $structure = new StructureRuleDefaults();
        $breathing = new BreathingRuleDefaults();
        $method = new MethodRuleDefaults();
        $naming = new NamingRuleDefaults();

        return new self(array_merge(
            $structure->defaults(),
            $breathing->defaults(),
            $method->defaults(),
            $naming->defaults(),
        ));
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
     * @return list<string>
     */
    public function ruleKeys(): array
    {
        return array_keys($this->rules);
    }
}
