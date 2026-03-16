<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\EvaluationContext\MethodMetricsData;
use Bunnivo\Soda\Quality\EvaluationContext\MethodNestingReturns;

/**
 * @psalm-param array<string, array{
 *   file_loc: int,
 *   classes_count: int,
 *   classes: array<string, array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, traits: int, interfaces: int, namespace: string, namespace_depth: int}>,
 *   methods: array<string, array{loc: int, args: int}>,
 *   namespaces: array<string, int>,
 *   breathing?: array<string, mixed>
 * }> $qualityMetrics
 */
final readonly class EvaluateInput
{
    public function __construct(
        public array $qualityMetrics,
        public MethodMetricsData $methodMetrics = new MethodMetricsData(),
    ) {}

    /**
     * @psalm-param array<string, positive-int> $complexityByMethod
     * @psalm-param array<string, array{depth: int, line: int, file: string}> $nestingByMethod
     * @psalm-param array<string, int> $returnsByMethod
     * @psalm-param array<string, list<array{line: int, count: int}>> $booleanConditionsByMethod
     */
    /**
     * @psalm-param array{
     *   complexity?: array<string, positive-int>,
     *   nesting?: array<string, array{depth: int, line: int, file: string}>,
     *   returns?: array<string, int>,
     *   conditions?: array<string, list<array{line: int, count: int}>>
     * } $methodData
     */
    public static function fromArrays(array $qualityMetrics, array $methodData = []): self
    {
        $nr = new MethodNestingReturns(
            $methodData['nesting'] ?? [],
            $methodData['returns'] ?? [],
        );
        $mm = new MethodMetricsData($nr, $methodData['conditions'] ?? [], $methodData['complexity'] ?? []);

        return new self($qualityMetrics, $mm);
    }
}
