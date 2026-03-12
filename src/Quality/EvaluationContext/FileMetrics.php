<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Bunnivo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda\Quality\EvaluationContext;

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
     * @psalm-param array<string, array{count: int, file: string}> $namespacesAggregated
     */
    public function __construct(
        public array $qualityMetrics,
        public array $complexityByMethod,
        public array $namespacesAggregated,
    ) {}
}
