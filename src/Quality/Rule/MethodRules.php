<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\EvaluationContext\MethodMetricsData;
use Bunnivo\Soda\Quality\EvaluationContext\MethodNestingReturns;
use Bunnivo\Soda\Quality\MethodChecker;
use Bunnivo\Soda\Quality\MethodCheckInput;
use Illuminate\Support\Collection;

final readonly class MethodRules implements RuleChecker
{
    public function __construct(
        private MethodChecker $methodChecker,
    ) {}

    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        $nestingReturns = new MethodNestingReturns(
            $context->fileMetrics->nestingByMethod(),
            $context->fileMetrics->returnsByMethod(),
        );
        $methodMetrics = new MethodMetricsData(
            $nestingReturns,
            $context->fileMetrics->booleanConditionsByMethod(),
            $context->fileMetrics->complexityByMethod(),
        );

        return collect($context->fileMetrics->qualityMetrics())
            ->flatMap(fn (array $data, string $file) => $this->methodChecker->check(new MethodCheckInput(
                $file,
                $data['methods'],
                $methodMetrics,
            )))
            ->values();
    }
}
