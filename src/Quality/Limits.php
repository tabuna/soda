<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

final readonly class Limits
{
    /**
     * @psalm-param positive-int $value
     * @psalm-param non-negative-int $threshold
     */
    public function __construct(
        public int $value,
        public int $threshold,
    ) {}
}
