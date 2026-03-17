<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\ClassChecker;
use Bunnivo\Soda\Quality\EvaluationContext;
use Illuminate\Support\Collection;

final readonly class ClassRules implements RuleChecker
{
    public function __construct(
        private ClassChecker $classChecker,
    ) {}

    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        $metrics = $context->fileMetrics->qualityMetrics();

        return collect($metrics)
            ->flatMap(fn (array $data, string $file) => $this->classChecker->check($file, $data['classes']))
            ->values();
    }
}
