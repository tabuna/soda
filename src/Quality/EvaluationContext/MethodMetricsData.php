<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EvaluationContext;

final readonly class MethodMetricsData
{
    public function __construct(
        public MethodNestingReturns $nestingReturns = new MethodNestingReturns(),
        public MethodScalarMetrics $scalarMetrics = new MethodScalarMetrics,
    ) {}

    public function nestingByMethod(): array
    {
        return $this->nestingReturns->nestingByMethod;
    }

    public function returnsByMethod(): array
    {
        return $this->nestingReturns->returnsByMethod;
    }

    /**
     * @psalm-return array<string, list<array{line: int, count: int}>>
     */
    public function booleanConditionsByMethod(): array
    {
        return $this->scalarMetrics->booleanConditionsByMethod;
    }

    /**
     * @psalm-return array<string, positive-int>
     */
    public function complexityByMethod(): array
    {
        return $this->scalarMetrics->complexityByMethod;
    }

    /**
     * @psalm-return array<string, int>
     */
    public function tryCatchByMethod(): array
    {
        return $this->scalarMetrics->tryCatchByMethod;
    }
}
