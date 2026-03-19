<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use function explode;
use function str_contains;

/**
 * @internal
 */
final readonly class CallableRuleViolationAssembler
{
    public function __construct(
        private QualityConfig $config,
    ) {}

    /**
     * @psalm-param array{loc: int, args: int} $callableShape
     *
     * @return list<Violation>
     */
    public function collectForCallable(MethodCheckInput $input, string $callableName, array $callableShape): array
    {
        $callableLabel = [
            'fullName' => $callableName,
            'class'    => str_contains($callableName, '::') ? explode('::', $callableName)[0] : null,
            'loc'      => $callableShape['loc'],
            'args'     => $callableShape['args'],
        ];

        $flowChecks = new CallableRuleFlowViolationChecks($this->config);

        return array_merge(
            $this->violationsForSizeAndArity($input->file, $callableLabel),
            $flowChecks->collect($input, $callableName, $callableLabel),
        );
    }

    /**
     * @psalm-param array{fullName: string, class: string|null, loc: int, args: int} $callableLabel
     *
     * @return list<Violation>
     */
    private function violationsForSizeAndArity(string $file, array $callableLabel): array
    {
        $maxLoc = (int) $this->config->getRule('max_method_length');

        $maxArgs = (int) $this->config->getRule('max_arguments');

        return array_merge(
            $this->checkLoc($file, $callableLabel, $maxLoc),
            $this->checkArgs($file, $callableLabel, $maxArgs),
        );
    }

    /**
     * @psalm-param array{fullName: string, class: string|null, loc: int, args?: int} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkLoc(string $file, array $info, int $max): array
    {
        if (! $this->config->isRuleEnabled('max_method_length')) {
            return [];
        }

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
        if (! $this->config->isRuleEnabled('max_arguments')) {
            return [];
        }

        return $this
            ->whenExceeded('max_arguments')
            ->file($file)
            ->method($info['fullName'])
            ->class($info['class'])
            ->forValue($info['args'])
            ->limit($max)
            ->result();
    }

    private function whenExceeded(string $rule): RuleChecker
    {
        return RuleChecker::whenExceeded($rule);
    }
}
