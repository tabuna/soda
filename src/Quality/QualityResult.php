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

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Result;

final readonly class QualityResult
{
    /**
     * @psalm-var non-negative-int
     */
    public int $score;

    /**
     * @psalm-var list<Violation>
     */
    public array $violations;

    /**
     * @psalm-param non-negative-int $score
     * @psalm-param list<Violation> $violations
     */
    public function __construct(
        public Result $metrics,
        int $score,
        array $violations,
    ) {
        $this->score = max(0, min(100, $score));
        $this->violations = $violations;
    }

    public function passes(int $minScore): bool
    {
        return $this->score >= $minScore;
    }
}
