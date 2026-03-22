<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Report;

use function basename;

use Bunnivo\Soda\Quality\QualityResult;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;

use function sprintf;
use function str_repeat;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;

use Symfony\Component\Console\Formatter\OutputFormatter;

final readonly class ReportFormatter
{
    private const int SEPARATOR_WIDTH = 60;

    public function __construct(
        private RuleMetadata $ruleMetadata,
    ) {}

    public function write(OutputStyle $output, QualityResult $result, string $projectRoot): void
    {
        $output->writeln([
            '<fg=bright-blue;options=bold>Soda Quality</>',
            '<fg=gray>'.str_repeat('-', self::SEPARATOR_WIDTH).'</>',
        ]);
        $output->newLine();

        $n = $result->violations->count();
        if ($n > 0) {
            $output->writeln(sprintf(
                '<fg=default>%s</>',
                OutputFormatter::escape($n === 1 ? '1 issue' : $n.' issues'),
            ));
            $output->newLine();

            $this->writeViolationsGrouped($output, $result->violations, $projectRoot);
            $output->writeln('<fg=gray>'.str_repeat('-', self::SEPARATOR_WIDTH).'</>');
            $output->newLine();
        }

        $passed = $result->isPassing();
        if ($passed) {
            $output->writeln('<fg=green;options=bold>[OK]</> No issues');
        } else {
            $output->writeln(sprintf(
                '<fg=red;options=bold>[FAIL]</> %s',
                $n === 1 ? '1 issue' : $n.' issues',
            ));
        }
    }

    /**
     * @param Collection<int, Violation> $violations
     */
    private function writeViolationsGrouped(OutputStyle $output, Collection $violations, string $projectRoot): void
    {
        /** @var Collection<string, Collection<int, Violation>> $byFile */
        $byFile = $violations->groupBy(fn (Violation $v) => $v->file)->sortKeys();

        foreach ($byFile as $file => $list) {
            $rel = $this->shortenPath($file, $projectRoot);
            $output->writeln('<fg=cyan;options=bold>'.OutputFormatter::escape($rel).'</>');

            foreach ($list as $v) {
                $this->writeViolationDetail($output, $v);
            }

            $output->newLine();
        }
    }

    private function writeViolationDetail(OutputStyle $output, Violation $v): void
    {
        $isError = $this->ruleMetadata->severity($v->rule) === RuleMetadata::SEVERITY_ERROR;
        $mark = $isError ? '<fg=red;options=bold>×</>' : '<fg=yellow;options=bold>!</>';

        $lineCol = $v->line() !== null ? (string) $v->line() : '—';
        $scope = $v->method() ?? $v->class();
        $locLine = '<options=bold>Line '.$lineCol.'</>';
        if ($scope !== null) {
            $locLine .= ' <fg=gray>('.OutputFormatter::escape($scope).')</>';
        }

        $detail = $v->context->message !== null
            ? OutputFormatter::escape($v->context->message)
            : $this->formatThresholdDetail($v);
        $output->writeln(sprintf('  %s %s', $mark, $locLine));
        $output->writeln('    '.$detail);
    }

    private function formatThresholdDetail(Violation $v): string
    {
        $label = OutputFormatter::escape($this->ruleMetadata->label($v->rule));
        $lim = $v->limits();
        $valueStr = OutputFormatter::escape($this->formatLimitValue($v->rule, $lim['value'], $lim['threshold']));

        return sprintf('<fg=default>%s</> %s', $label, $valueStr);
    }

    private function shortenPath(string $path, string $root): string
    {
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $path = str_replace('\\', '/', $path);
        if ($root !== '' && (str_starts_with($path, $root.'/') || $path === $root)) {
            $cut = substr($path, strlen($root) + 1);

            return $cut !== '' && $cut !== false ? $cut : basename($path);
        }

        return $path;
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
