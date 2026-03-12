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

use const PHP_EOL;

use function sprintf;

final readonly class QualityReportFormatter
{
    public function __construct(
        private RuleMetadata $ruleMetadata,
    ) {}

    public function format(QualityResult $result): string
    {
        $buffer = <<<'EOT'
Code Quality Report
──────────────────

EOT;

        $buffer .= sprintf('Score: %d / 100'.PHP_EOL.PHP_EOL, $result->score);

        if ($result->violations !== []) {
            $buffer .= 'Violations'.PHP_EOL.'──────────'.PHP_EOL.PHP_EOL;

            foreach ($result->violations as $v) {
                $buffer .= $this->formatViolation($v);
            }

            $buffer .= PHP_EOL;
        }

        $buffer .= 'Summary'.PHP_EOL.'───────'.PHP_EOL.PHP_EOL;
        $buffer .= sprintf('Violations: %d'.PHP_EOL, count($result->violations));
        $buffer .= sprintf('Score: %d'.PHP_EOL, $result->score);

        return $buffer;
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
