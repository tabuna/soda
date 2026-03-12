<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\MethodChecker;
use Illuminate\Support\Collection;

final class MethodRules implements RuleChecker
{
    public function __construct(
        private readonly MethodChecker $methodChecker,
    ) {}

    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        return collect($context->fileMetrics->qualityMetrics)
            ->flatMap(fn (array $data, string $file) => $this->methodChecker->check(
                $file,
                $data['methods'],
                $context->fileMetrics->complexityByMethod,
            ))
            ->values();
    }
}
