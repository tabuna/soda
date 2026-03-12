<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

/**
 * @internal
 */
final readonly class BreathingFactors
{
    public function __construct(
        private CognitiveLoad $cognitive,
        private AirinessFactors $airiness,
    ) {}

    public function cognitive(): CognitiveLoad
    {
        return $this->cognitive;
    }

    public function airiness(): AirinessFactors
    {
        return $this->airiness;
    }
}
