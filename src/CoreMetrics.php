<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

/**
 * @internal
 */
final readonly class CoreMetrics
{
    public function __construct(
        private LocMetrics $loc,
        private ComplexityMetrics $complexity,
    ) {}

    public function loc(): LocMetrics
    {
        return $this->loc;
    }

    public function complexity(): ComplexityMetrics
    {
        return $this->complexity;
    }
}
