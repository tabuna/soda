<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Bunnivo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Limits;
use Bunnivo\Soda\Quality\Violation;
use Bunnivo\Soda\Quality\ViolationBuilder;

final class NamespaceChecker implements RuleChecker
{
    /**
     * @return Violation[]
     *
     * @psalm-return list<Bunnivo\Soda\Quality\Violation>
     */
    #[\Override]
    public function check(EvaluationContext $context): array
    {
        $violations = [];
        $maxDepth = $context->config->getRule('max_namespace_depth');
        $maxPerNamespace = $context->config->getRule('max_classes_per_namespace');

        foreach ($context->fileMetrics->qualityMetrics as $file => $data) {
            foreach ($data['classes'] ?? [] as $className => $classData) {
                if ($maxDepth > 0 && $classData['namespace_depth'] > $maxDepth) {
                    $violations[] = ViolationBuilder::of(
                        'max_namespace_depth',
                        $file,
                        new Limits($classData['namespace_depth'], $maxDepth),
                    )->atClass($className)->build();
                }
            }
        }

        foreach ($context->fileMetrics->namespacesAggregated as $namespace => $data) {
            $count = $data['count'];
            $file = $data['file'];
            if ($maxPerNamespace > 0 && $count > $maxPerNamespace) {
                $violations[] = ViolationBuilder::of(
                    'max_classes_per_namespace',
                    $file,
                    new Limits($count, $maxPerNamespace),
                )->atClass($namespace)->build();
            }
        }

        return $violations;
    }
}
