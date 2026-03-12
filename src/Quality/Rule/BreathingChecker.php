<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\RuleChecker as FluentRuleChecker;
use Bunnivo\Soda\Quality\Violation;
use Illuminate\Support\Collection;

final class BreathingChecker implements RuleChecker
{
    private const MIN_RULES = [
        'min_code_breathing_score'             => 'cbs',
        'min_visual_breathing_index'           => 'vbi',
        'min_identifier_readability_score'     => 'irs',
        'min_code_oxygen_level'                => 'col',
    ];

    private const MAX_RULES = [
        'max_weighted_cognitive_density'       => 'wcd',
        'max_logical_complexity_factor'        => 'lcf',
    ];

    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        /** @var list<Violation> $list */
        $list = [];

        foreach ($context->fileMetrics->qualityMetrics as $file => $data) {
            $breathing = $data['breathing'] ?? null;
            if ($breathing === null) {
                continue;
            }

            $list = array_merge($list, $this->checkMinRules($context, $file, $breathing));
            $list = array_merge($list, $this->checkMaxRules($context, $file, $breathing));
        }

        return collect($list)->values();
    }

    /**
     * @param array<string, float> $breathing
     *
     * @return list<Violation>
     */
    private function checkMinRules(EvaluationContext $context, string $file, array $breathing): array
    {
        $list = [];

        foreach (self::MIN_RULES as $rule => $key) {
            $threshold = (int) $context->config->getRule($rule);
            if ($threshold <= 0) {
                continue;
            }

            $value = $breathing[$key] ?? 0.0;
            $scaled = (int) round($value * 100.0);

            if ($scaled < $threshold) {
                foreach (FluentRuleChecker::whenBelow($rule)->file($file)->forValue($scaled)->limit($threshold)->result() as $v) {
                    $list[] = $v;
                }
            }
        }

        return $list;
    }

    /**
     * @param array<string, float> $breathing
     *
     * @return list<Violation>
     */
    private function checkMaxRules(EvaluationContext $context, string $file, array $breathing): array
    {
        $list = [];

        foreach (self::MAX_RULES as $rule => $key) {
            $threshold = (int) $context->config->getRule($rule);
            if ($threshold <= 0) {
                continue;
            }

            $value = $breathing[$key] ?? 0.0;
            $scaled = $key === 'lcf' ? (int) round($value * 10.0) : (int) round($value);

            if ($scaled > $threshold) {
                foreach (FluentRuleChecker::whenExceeded($rule)->file($file)->forValue($scaled)->limit($threshold)->result() as $v) {
                    $list[] = $v;
                }
            }
        }

        return $list;
    }
}
