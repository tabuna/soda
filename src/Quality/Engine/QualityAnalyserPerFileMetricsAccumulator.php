<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Engine;

use Bunnivo\Soda\Quality\EvaluationContext\MethodMetricsData;
use Bunnivo\Soda\Quality\EvaluationContext\MethodNestingReturns;
use Bunnivo\Soda\Quality\EvaluationContext\MethodScalarMetrics;
use SebastianBergmann\Complexity\ComplexityCollection;

/**
 * @internal
 */
final class QualityAnalyserPerFileMetricsAccumulator
{
    /** @var array<string, array<string, mixed>> */
    private array $qualityMetricsByFile = [];

    /**
     * @var array{
     *     complexity: array<string, positive-int>,
     *     nesting: array<string, array{depth: int, line: int, file: string}>,
     *     returns: array<string, int>,
     *     booleanConditions: array<string, list<array{line: int, count: int}>>,
     *     tryCatch: array<string, int>
     * }
     */
    private array $methodKeyedMetricBuckets = [
        'complexity'        => [],
        'nesting'           => [],
        'returns'           => [],
        'booleanConditions' => [],
        'tryCatch'          => [],
    ];

    /**
     * @psalm-param array{
     *     metrics: array,
     *     complexity: ComplexityCollection,
     *     nesting: array<string, array{depth: int, line: int}>,
     *     returns: array<string, int>,
     *     booleanConditions: array<string, list<array{line: int, count: int}>>,
     *     tryCatch: array<string, int>
     * } $extracted
     */
    public function mergeFile(string $file, array $extracted): void
    {
        $this->qualityMetricsByFile[$file] = $extracted['metrics'];

        foreach ($extracted['complexity']->asArray() as $item) {
            $this->methodKeyedMetricBuckets['complexity'][$item->name()] = $item->cyclomaticComplexity();
        }

        foreach ($extracted['nesting'] as $method => $data) {
            $this->methodKeyedMetricBuckets['nesting'][$method] = array_merge($data, ['file' => $file]);
        }

        foreach ($extracted['returns'] as $method => $count) {
            $this->methodKeyedMetricBuckets['returns'][$method] = $count;
        }

        foreach ($extracted['booleanConditions'] as $method => $conditions) {
            $this->methodKeyedMetricBuckets['booleanConditions'][$method] = $conditions;
        }

        foreach ($extracted['tryCatch'] as $method => $count) {
            $this->methodKeyedMetricBuckets['tryCatch'][$method] = $count;
        }
    }

    public function evaluateInput(): EvaluateInput
    {
        $buckets = $this->methodKeyedMetricBuckets;

        $nestingReturns = new MethodNestingReturns($buckets['nesting'], $buckets['returns']);

        return new EvaluateInput($this->qualityMetricsByFile, new MethodMetricsData(
            $nestingReturns,
            new MethodScalarMetrics($buckets['booleanConditions'], $buckets['complexity'], $buckets['tryCatch']),
        ));
    }
}
