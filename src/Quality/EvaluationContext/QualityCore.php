<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EvaluationContext;

/**
 * @psalm-param array<string, array{
 *   file_loc: int,
 *   classes_count: int,
 *   classes: array<string, array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, efferent_coupling: int, traits: int, interfaces: int, namespace: string, namespace_depth: int}>,
 *   methods: array<string, array{loc: int, args: int}>,
 *   namespaces: array<string, int>,
 *   breathing?: array<string, mixed>
 * }> $qualityMetrics
 * @psalm-param array<string, positive-int> $complexityByMethod
 */
final readonly class QualityCore
{
    public function __construct(
        public array $qualityMetrics,
        public array $complexityByMethod,
    ) {}
}
