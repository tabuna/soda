<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EvaluationContext;

/**
 * Per-method scalar collections (conditions, complexity, try/catch) separate from nesting/returns.
 *
 * @psalm-type BooleanConditions=array<string, list<array{line: int, count: int}>>
 */
final readonly class MethodScalarMetrics
{
    /**
     * @param BooleanConditions           $booleanConditionsByMethod
     * @param array<string, positive-int> $complexityByMethod
     * @param array<string, int>          $tryCatchByMethod
     */
    public function __construct(
        public array $booleanConditionsByMethod = [],
        public array $complexityByMethod = [],
        public array $tryCatchByMethod = [],
    ) {}
}
