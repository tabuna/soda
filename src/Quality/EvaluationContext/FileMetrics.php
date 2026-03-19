<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EvaluationContext;

use Illuminate\Support\Collection;

final readonly class FileMetrics
{
    /**
     * @psalm-param Collection<string, array{count: int, file: string}> $namespacesAggregated
     */
    public function __construct(
        public QualityCore $core,
        public Collection $namespacesAggregated,
        public MethodMetricsData $methodMetrics = new MethodMetricsData(),
    ) {}

    public function qualityMetrics(): array
    {
        return $this->core->qualityMetrics;
    }

    public function complexityByMethod(): array
    {
        return $this->core->complexityByMethod;
    }

    public function nestingByMethod(): array
    {
        return $this->methodMetrics->nestingByMethod();
    }

    public function returnsByMethod(): array
    {
        return $this->methodMetrics->returnsByMethod();
    }

    public function booleanConditionsByMethod(): array
    {
        return $this->methodMetrics->booleanConditionsByMethod();
    }

    /**
     * @psalm-return array<string, int>
     */
    public function tryCatchByMethod(): array
    {
        return $this->methodMetrics->tryCatchByMethod();
    }
}
