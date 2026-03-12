<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\RuleChecker as FluentRuleChecker;
use Illuminate\Support\Collection;

final class NamespaceChecker implements RuleChecker
{
    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        $maxDepth = $context->config->getRule('max_namespace_depth');
        $maxPerNamespace = $context->config->getRule('max_classes_per_namespace');

        $depthViolations = collect($context->fileMetrics->qualityMetrics)
            ->flatMap(function (array $data, string $file) use ($maxDepth) {
                return collect($data['classes'] ?? [])
                    ->flatMap(function (array $classData, string $className) use ($file, $maxDepth) {
                        return FluentRuleChecker::whenExceeded('max_namespace_depth')
                            ->file($file)
                            ->class($className)
                            ->forValue($classData['namespace_depth'])
                            ->limit($maxDepth)
                            ->result();
                    });
            })
            ->values();

        $namespacesViolations = $context->fileMetrics->namespacesAggregated
            ->flatMap(function (array $data, string $namespace) use ($maxPerNamespace) {
                return FluentRuleChecker::whenExceeded('max_classes_per_namespace')
                    ->file($data['file'])
                    ->class($namespace)
                    ->forValue($data['count'])
                    ->limit($maxPerNamespace)
                    ->result();
            })
            ->values();

        return $depthViolations->merge($namespacesViolations);
    }
}
