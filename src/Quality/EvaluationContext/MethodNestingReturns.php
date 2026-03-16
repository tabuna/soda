<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EvaluationContext;

/**
 * @psalm-param array<string, array{depth: int, line: int, file: string}> $nestingByMethod
 * @psalm-param array<string, int> $returnsByMethod
 */
final readonly class MethodNestingReturns
{
    public function __construct(
        public array $nestingByMethod = [],
        public array $returnsByMethod = [],
    ) {}
}
