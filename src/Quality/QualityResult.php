<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Result;
use Illuminate\Support\Collection;

final readonly class QualityResult
{
    /**
     * @psalm-var Collection<int, Violation>
     */
    public Collection $violations;

    /**
     * @psalm-param Collection<int, Violation>|list<Violation> $violations
     */
    public function __construct(
        public Result $metrics,
        Collection|array $violations,
    ) {
        /** @var Collection<int, Violation> $col */
        $col = $violations instanceof Collection ? $violations : collect($violations);
        $this->violations = $col;
    }

    public function passes(): bool
    {
        return $this->violations->isEmpty();
    }
}
