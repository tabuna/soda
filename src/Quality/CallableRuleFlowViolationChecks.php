<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

/**
 * @internal
 */
final readonly class CallableRuleFlowViolationChecks
{
    public function __construct(
        private QualityConfig $config,
    ) {}

    /**
     * @psalm-param array{fullName: string, class: string|null, loc: int, args: int} $callableLabel
     *
     * @return list<Violation>
     */
    public function collect(MethodCheckInput $input, string $callableName, array $callableLabel): array
    {
        $file = $input->file;

        $metrics = $input->methodMetrics;

        return array_merge(
            $this->checkComplexity($file, $callableLabel, $metrics->complexityByMethod()[$callableName] ?? 1),
            $this->checkNesting($file, $callableLabel, $metrics->nestingByMethod()[$callableName] ?? null),
            $this->checkReturns($file, $callableLabel, $metrics->returnsByMethod()[$callableName] ?? 0),
            $this->checkBooleanConditions($file, $callableLabel, $metrics->booleanConditionsByMethod()[$callableName] ?? []),
            $this->checkTryCatch($file, $callableLabel, $metrics->tryCatchByMethod()[$callableName] ?? 0),
        );
    }

    /**
     * @psalm-param array{fullName: string, class: string|null, loc?: int, args?: int} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkComplexity(string $file, array $info, int $complexity): array
    {
        if (! $this->config->isRuleEnabled('max_cyclomatic_complexity')) {
            return [];
        }

        $maxComplexity = $this->config->getRule('max_cyclomatic_complexity');

        return $this
            ->whenExceeded('max_cyclomatic_complexity')
            ->file($file)
            ->method($info['fullName'])
            ->class($info['class'])
            ->forValue($complexity)
            ->limit($maxComplexity)
            ->result();
    }

    /**
     * @psalm-param array{fullName: string, class: string|null} $info
     * @psalm-param array{depth: int, line: int, file: string}|null $nesting
     *
     * @psalm-return list<Violation>
     */
    private function checkNesting(string $file, array $info, ?array $nesting): array
    {
        if (! $this->config->isRuleEnabled('max_control_nesting')) {
            return [];
        }

        if ($nesting === null || $nesting['file'] !== $file) {
            return [];
        }

        $maxNesting = (int) $this->config->getRule('max_control_nesting');
        if ($maxNesting <= 0) {
            return [];
        }

        return $this
            ->whenExceeded('max_control_nesting')
            ->file($file)
            ->method($info['fullName'])
            ->class($info['class'])
            ->line($nesting['line'])
            ->forValue($nesting['depth'])
            ->limit($maxNesting)
            ->result();
    }

    /**
     * @psalm-param array{fullName: string, class: string|null} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkReturns(string $file, array $info, int $returns): array
    {
        if (! $this->config->isRuleEnabled('max_return_statements')) {
            return [];
        }

        $max = (int) $this->config->getRule('max_return_statements');
        if ($max <= 0) {
            return [];
        }

        return $this
            ->whenExceeded('max_return_statements')
            ->file($file)
            ->method($info['fullName'])
            ->class($info['class'])
            ->forValue($returns)
            ->limit($max)
            ->result();
    }

    /**
     * @psalm-param array{fullName: string, class: string|null} $info
     * @psalm-param list<array{line: int, count: int}> $conditions
     *
     * @psalm-return list<Violation>
     */
    private function checkBooleanConditions(string $file, array $info, array $conditions): array
    {
        if (! $this->config->isRuleEnabled('max_boolean_conditions')) {
            return [];
        }

        $max = (int) $this->config->getRule('max_boolean_conditions');
        if ($max <= 0) {
            return [];
        }

        return CallableRuleBooleanConditionViolationExpander::expand([
            'fluent'       => RuleChecker::whenExceeded('max_boolean_conditions'),
            'file'         => $file,
            'callableInfo' => $info,
            'conditions'   => $conditions,
            'max'          => $max,
        ]);
    }

    /**
     * @psalm-param array{fullName: string, class: string|null} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkTryCatch(string $file, array $info, int $count): array
    {
        if (! $this->config->isRuleEnabled('max_try_catch_blocks')) {
            return [];
        }

        $max = (int) $this->config->getRule('max_try_catch_blocks');
        if ($max <= 0) {
            return [];
        }

        return $this
            ->whenExceeded('max_try_catch_blocks')
            ->file($file)
            ->method($info['fullName'])
            ->class($info['class'])
            ->forValue($count)
            ->limit($max)
            ->result();
    }

    private function whenExceeded(string $rule): RuleChecker
    {
        return RuleChecker::whenExceeded($rule);
    }
}
