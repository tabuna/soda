<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\RuleChecker as FluentRuleChecker;
use Illuminate\Support\Collection;

final class LocChecker implements RuleChecker
{
    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        $threshold = $context->config->getRule('max_file_loc');

        return collect($context->fileMetrics->qualityMetrics)
            ->flatMap(function (array $data, string $file) use ($threshold) {
                return FluentRuleChecker::whenExceeded('max_file_loc')
                    ->file($file)
                    ->forValue($data['file_loc'])
                    ->limit($threshold)
                    ->result();
            })
            ->values();
    }
}
