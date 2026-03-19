<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use const PHP_EOL;

use function sprintf;

final readonly class ReportFormatter
{
    public function __construct(
        private RuleMetadata $ruleMetadata,
    ) {}

    public function format(QualityResult $result): string
    {
        $buf = <<<'EOT'
Code Quality Report
──────────────────

EOT;

        $buf .= sprintf('Score: %d / 100'.PHP_EOL.PHP_EOL, $result->score);

        if ($result->violations->isNotEmpty()) {
            $buf .= 'Violations'.PHP_EOL.'──────────'.PHP_EOL.PHP_EOL;
            $buf .= $result->violations
                ->map(fn (Violation $v) => $this->formatViolation($v))
                ->implode('');
            $buf .= PHP_EOL;
        }

        $buf .= 'Summary'.PHP_EOL.'───────'.PHP_EOL.PHP_EOL;
        $buf .= sprintf('Violations: %d'.PHP_EOL, $result->violations->count());

        return $buf.sprintf('Score: %d'.PHP_EOL, $result->score);
    }

    private function formatViolation(Violation $v): string
    {
        $icon = $this->ruleMetadata->severity($v->rule) === RuleMetadata::SEVERITY_ERROR ? '❌' : '⚠️';
        $target = $v->method() ?? $v->class() ?? $v->file;
        if ($v->line() !== null) {
            $target = $v->file.':'.$v->line();
        }

        if ($v->context->message !== null) {
            return sprintf(
                '%s %s'.PHP_EOL.'   %s'.PHP_EOL.PHP_EOL,
                $icon,
                $target,
                $v->context->message,
            );
        }

        $label = $this->ruleMetadata->label($v->rule);
        $lim = $v->limits();

        $valueStr = $this->formatLimitValue($v->rule, $lim['value'], $lim['threshold']);

        return sprintf(
            '%s %s'.PHP_EOL.'   %s %s'.PHP_EOL.PHP_EOL,
            $icon,
            $target,
            $label,
            $valueStr,
        );
    }

    private const array MIN_BREATHING_RULES = [
        'min_code_breathing_score',
        'min_visual_breathing_index',
        'min_identifier_readability_score',
        'min_code_oxygen_level',
    ];

    private function formatLimitValue(string $rule, int $value, int $threshold): string
    {
        if (in_array($rule, self::MIN_BREATHING_RULES, true)) {
            return sprintf('%.2f (required ≥ %.2f)', $value / 100, $threshold / 100);
        }

        if ($rule === 'max_logical_complexity_factor') {
            return sprintf('%.1f (max %.1f)', $value / 10, $threshold / 10);
        }

        if ($rule === 'max_weighted_cognitive_density') {
            return sprintf('%d (max %d)', $value, $threshold);
        }

        $cmp = $this->ruleMetadata->comparison($rule);

        return sprintf('%d (%s %d)', $value, $cmp, $threshold);
    }
}
