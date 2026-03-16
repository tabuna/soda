<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\EvaluationContext\MethodMetricsData;

/**
 * @psalm-param array<string, array{loc: int, args: int}> $methods
 */
final readonly class MethodCheckInput
{
    public function __construct(
        public string $file,
        public array $methods,
        public MethodMetricsData $methodMetrics = new MethodMetricsData(),
    ) {}
}
