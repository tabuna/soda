<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

/**
 * @internal
 */
final readonly class AirinessFactors
{
    public function __construct(
        public float $vbi,
        public float $irs,
        public float $col,
    ) {}
}
