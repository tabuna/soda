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

final class FileLocChecker implements RuleChecker
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
        $threshold = $context->config->getRule('max_file_loc');

        foreach ($context->fileMetrics->qualityMetrics as $file => $data) {
            $loc = $data['file_loc'];
            if ($threshold > 0 && $loc > $threshold) {
                $violations[] = ViolationBuilder::of('max_file_loc', $file, new Limits($loc, $threshold))->build();
            }
        }

        return $violations;
    }
}
