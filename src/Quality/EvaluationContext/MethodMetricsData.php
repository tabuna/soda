<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EvaluationContext;

/**
 * @psalm-param array<string, list<array{line: int, count: int}>> $booleanConditionsByMethod
 * @psalm-param array<string, positive-int> $complexityByMethod
 */
final readonly class MethodMetricsData
{
    public function __construct(
        public MethodNestingReturns $nestingReturns = new MethodNestingReturns(),
        public array $booleanConditionsByMethod = [],
        public array $complexityByMethod = [],
    ) {}

    public function nestingByMethod(): array
    {
        return $this->nestingReturns->nestingByMethod;
    }

    public function returnsByMethod(): array
    {
        return $this->nestingReturns->returnsByMethod;
    }
}
