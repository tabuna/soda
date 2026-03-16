<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\RuleChecker as FluentRuleChecker;
use Illuminate\Support\Collection;

final class ClassesChecker implements RuleChecker
{
    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        $threshold = $context->config->getRule('max_classes_per_file');

        return collect($context->fileMetrics->qualityMetrics())
            ->flatMap(function (array $data, string $file) use ($threshold) {
                return FluentRuleChecker::whenExceeded('max_classes_per_file')
                    ->file($file)
                    ->forValue($data['classes_count'])
                    ->limit($threshold)
                    ->result();
            })
            ->values();
    }
}
