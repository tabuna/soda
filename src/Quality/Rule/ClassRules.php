<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\ClassChecker;
use Bunnivo\Soda\Quality\EvaluationContext;
use Illuminate\Support\Collection;

final class ClassRules implements RuleChecker
{
    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        $checker = new ClassChecker($context->config);

        return collect($context->fileMetrics->qualityMetrics())
            ->flatMap(fn (array $data, string $file) => $checker->check($file, $data['classes']))
            ->values();
    }
}
