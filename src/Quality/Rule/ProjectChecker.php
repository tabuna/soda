<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use function array_key_first;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\RuleChecker as FluentRuleChecker;
use Bunnivo\Soda\Quality\Violation;
use Illuminate\Support\Collection;

final class ProjectChecker implements RuleChecker
{
    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        $threshold = $context->config->getRule('max_classes_per_project');
        $total = $context->projectMetrics->classesOrTraits();
        $firstFile = array_key_first($context->fileMetrics->qualityMetrics) ?? '.';

        $violations = FluentRuleChecker::whenExceeded('max_classes_per_project')
            ->file($firstFile)
            ->forValue($total)
            ->limit($threshold)
            ->result();

        /** @var Collection<int, Violation> */
        return collect($violations);
    }
}
