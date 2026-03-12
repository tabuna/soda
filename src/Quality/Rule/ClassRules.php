<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\ClassChecker;
use Bunnivo\Soda\Quality\EvaluationContext;
use Illuminate\Support\Collection;

final class ClassRules implements RuleChecker
{
    public function __construct(
        private readonly ClassChecker $classChecker,
    ) {}

    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        return collect($context->fileMetrics->qualityMetrics)
            ->flatMap(fn (array $data, string $file) => $this->classChecker->check($file, $data['classes']))
            ->values();
    }
}
