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
        $buf .= sprintf('Score: %d'.PHP_EOL, $result->score);

        return $buf;
    }

    private function formatViolation(Violation $v): string
    {
        $icon = $this->ruleMetadata->severity($v->rule) === RuleMetadata::SEVERITY_ERROR ? '❌' : '⚠️';
        $target = $v->method() ?? $v->class() ?? $v->file;
        $label = $this->ruleMetadata->label($v->rule);
        $lim = $v->limits();

        return sprintf(
            '%s %s'.PHP_EOL.'   %s %d (max %d)'.PHP_EOL.PHP_EOL,
            $icon,
            $target,
            $label,
            $lim['value'],
            $lim['threshold'],
        );
    }
}
