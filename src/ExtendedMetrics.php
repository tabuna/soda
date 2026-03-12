<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Breathing\BreathingMetrics;
use Bunnivo\Soda\Structure\Metrics;

/**
 * @internal
 */
final readonly class ExtendedMetrics
{
    public function __construct(
        private ?Metrics $structure,
        private ?BreathingMetrics $breathing,
    ) {}

    public function structure(): ?Metrics
    {
        return $this->structure;
    }

    public function breathing(): ?BreathingMetrics
    {
        return $this->breathing;
    }
}
