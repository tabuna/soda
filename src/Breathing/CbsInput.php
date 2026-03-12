<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

/**
 * @internal
 */
final readonly class CbsInput
{
    public function __construct(
        private CognitiveLoad $cognitive,
        private AirinessFactors $airiness,
        private int $totalLines,
    ) {}

    public static function fromFactors(CognitiveLoad $cognitive, AirinessFactors $airiness, int $totalLines): self
    {
        return new self($cognitive, $airiness, $totalLines);
    }

    public function cognitive(): CognitiveLoad
    {
        return $this->cognitive;
    }

    public function airiness(): AirinessFactors
    {
        return $this->airiness;
    }

    public function totalLines(): int
    {
        return $this->totalLines;
    }
}
