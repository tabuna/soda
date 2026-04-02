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
            $complexity = $this->methodKeyedMetricBuckets['complexity'];
            $complexity[$item->name()] = $item->cyclomaticComplexity();
            $this->methodKeyedMetricBuckets['complexity'] = $complexity;
        }

        foreach ($extracted['nesting'] as $method => $data) {
            $nesting = $this->methodKeyedMetricBuckets['nesting'];
            $nesting[$method] = array_merge($data, ['file' => $file]);
            $this->methodKeyedMetricBuckets['nesting'] = $nesting;
        }

        foreach ($extracted['returns'] as $method => $count) {
            $returns = $this->methodKeyedMetricBuckets['returns'];
            $returns[$method] = $count;
            $this->methodKeyedMetricBuckets['returns'] = $returns;
        }

        foreach ($extracted['booleanConditions'] as $method => $conditions) {
            $boolean = $this->methodKeyedMetricBuckets['booleanConditions'];
            $boolean[$method] = $conditions;
            $this->methodKeyedMetricBuckets['booleanConditions'] = $boolean;
        }

        foreach ($extracted['tryCatch'] as $method => $count) {
            $tryCatch = $this->methodKeyedMetricBuckets['tryCatch'];
            $tryCatch[$method] = $count;
            $this->methodKeyedMetricBuckets['tryCatch'] = $tryCatch;
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
