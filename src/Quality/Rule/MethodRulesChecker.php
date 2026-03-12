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

use function array_merge;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\MethodChecker;
use Bunnivo\Soda\Quality\Violation;

final class MethodRulesChecker implements RuleChecker
{
    public function __construct(
        private readonly MethodChecker $methodChecker,
    ) {}

    /**
     * @return list<Violation>
     */
    #[\Override]
    public function check(EvaluationContext $context): array
    {
        $violations = [];

        foreach ($context->fileMetrics->qualityMetrics as $file => $data) {
            $violations = array_merge(
                $violations,
                $this->methodChecker->check($file, $data['methods'], $context->fileMetrics->complexityByMethod),
            );
        }

        return $violations;
    }
}
