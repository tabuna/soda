<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Result;
use Illuminate\Support\Collection;

final readonly class QualityResult
{
    /**
     * @psalm-var non-negative-int
     */
    public int $score;

    /**
     * @psalm-var Collection<int, Violation>
     */
    public Collection $violations;

    /**
     * @psalm-param int $score
     * @psalm-param Collection<int, Violation>|list<Violation> $violations
     */
    public function __construct(
        public Result $metrics,
        int $score,
        Collection|array $violations,
    ) {
        $this->score = max(0, min(100, $score));
        /** @var Collection<int, Violation> $col */
        $col = $violations instanceof Collection ? $violations : collect($violations);
        $this->violations = $col;
    }

    public function passes(int $minScore): bool
    {
        return $this->score >= $minScore;
    }
}
