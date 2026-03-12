<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EvaluationContext;

use Illuminate\Support\Collection;

final readonly class FileMetrics
{
    /**
     * @psalm-param array<string, array{
     *   file_loc: int,
     *   classes_count: int,
     *   classes: array<string, array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, traits: int, interfaces: int, namespace: string, namespace_depth: int}>,
     *   methods: array<string, array{loc: int, args: int}>,
     *   namespaces: array<string, int>
     * }> $qualityMetrics
     * @psalm-param array<string, positive-int> $complexityByMethod
     * @psalm-param Collection<string, array{count: int, file: string}> $namespacesAggregated
     */
    public function __construct(
        public array $qualityMetrics,
        public array $complexityByMethod,
        public Collection $namespacesAggregated,
    ) {}
}
