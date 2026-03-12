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

use function array_key_first;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Limits;
use Bunnivo\Soda\Quality\Violation;
use Bunnivo\Soda\Quality\ViolationBuilder;

final class ProjectChecker implements RuleChecker
{
    /**
     * @return Violation[]
     *
     * @psalm-return list{0?: Violation}
     */
    #[\Override]
    public function check(EvaluationContext $context): array
    {
        $threshold = $context->config->getRule('max_classes_per_project');
        if ($threshold <= 0) {
            return [];
        }

        $total = $context->projectMetrics->classesOrTraits();
        if ($total <= $threshold) {
            return [];
        }

        $firstFile = array_key_first($context->fileMetrics->qualityMetrics) ?? '.';

        return [
            ViolationBuilder::of('max_classes_per_project', $firstFile, new Limits($total, $threshold))->build(),
        ];
    }
}
