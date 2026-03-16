<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use function explode;

use Illuminate\Support\Collection;

use function str_contains;

final readonly class MethodChecker
{
    public function __construct(
        private QualityConfig $config,
    ) {}

    /**
     * @return Collection<int, Violation>
     */
    public function check(MethodCheckInput $input): Collection
    {
        $maxLoc = (int) $this->config->getRule('max_method_length');
        $maxArgs = (int) $this->config->getRule('max_arguments');
        $m = $input->methodMetrics;

        return collect($input->methods)
            ->flatMap(function (array $data, string $name) use ($input, $maxLoc, $maxArgs, $m) {
                $info = [
                    'fullName' => $name,
                    'class'    => str_contains($name, '::') ? explode('::', $name)[0] : null,
                    'loc'      => $data['loc'],
                    'args'     => $data['args'],
                ];

                return array_merge(
                    $this->checkLoc($input->file, $info, $maxLoc),
                    $this->checkArgs($input->file, $info, $maxArgs),
                    $this->checkComplexity($input->file, $info, $m->complexityByMethod[$name] ?? 1),
                    $this->checkNesting($input->file, $info, $m->nestingByMethod()[$name] ?? null),
                    $this->checkReturns($input->file, $info, $m->returnsByMethod()[$name] ?? 0),
                    $this->checkBooleanConditions($input->file, $info, $m->booleanConditionsByMethod[$name] ?? []),
                );
            })
            ->values();
    }

    /**
     * @psalm-param array{fullName: string, class: string|null, loc: int, args?: int} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkLoc(string $file, array $info, int $max): array
    {
        return $this
            ->whenExceeded('max_method_length')
            ->file($file)
            ->method($info['fullName'])
            ->class($info['class'])
            ->forValue($info['loc'])
            ->limit($max)
            ->result();
    }

    /**
     * @psalm-param array{fullName: string, class: string|null, args: int, loc?: int} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkArgs(string $file, array $info, int $max): array
    {
        return $this
            ->whenExceeded('max_arguments')
            ->file($file)
            ->method($info['fullName'])
            ->class($info['class'])
            ->forValue($info['args'])
            ->limit($max)
            ->result();
    }

    /**
     * @psalm-param array{fullName: string, class: string|null, loc?: int, args?: int} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkComplexity(string $file, array $info, int $complexity): array
    {
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
        $max = (int) $this->config->getRule('max_boolean_conditions');
        if ($max <= 0) {
            return [];
        }

        $violations = [];
        foreach ($conditions as $cond) {
            if ($cond['count'] > $max) {
                foreach ($this
                    ->whenExceeded('max_boolean_conditions')
                    ->file($file)
                    ->method($info['fullName'])
                    ->class($info['class'])
                    ->line($cond['line'])
                    ->forValue($cond['count'])
                    ->limit($max)
                    ->result() as $v) {
                    $violations[] = $v;
                }
            }
        }

        return $violations;
    }

    private function whenExceeded(string $rule): RuleChecker
    {
        return RuleChecker::whenExceeded($rule);
    }
}
