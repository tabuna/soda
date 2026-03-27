<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\EvaluationContext\MethodMetricsData;
use Bunnivo\Soda\Quality\EvaluationContext\MethodNestingReturns;
use Bunnivo\Soda\Quality\EvaluationContext\MethodScalarMetrics;
use Bunnivo\Soda\Quality\MethodChecker;
use Bunnivo\Soda\Quality\MethodCheckInput;
use Illuminate\Support\Collection;

final class MethodRules implements RuleChecker
{
    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        $checker = new MethodChecker($context->config);

        $nestingReturns = new MethodNestingReturns(
            $context->fileMetrics->nestingByMethod(),
            $context->fileMetrics->returnsByMethod(),
        );

        $methodMetrics = new MethodMetricsData(
            $nestingReturns,
            new MethodScalarMetrics(
                $context->fileMetrics->booleanConditionsByMethod(),
                $context->fileMetrics->complexityByMethod(),
                $context->fileMetrics->tryCatchByMethod(),
            ),
        );

        return collect($context->fileMetrics->qualityMetrics())
            ->flatMap(fn (array $data, string $file) => $checker->check(new MethodCheckInput(
                $file,
                $data['methods'],
                $methodMetrics,
            )))
            ->values();
    }
}
