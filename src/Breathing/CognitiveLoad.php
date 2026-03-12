<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

/**
 * @internal
 */
final readonly class CognitiveLoad
{
    public function __construct(
        public float $wcd,
        public float $lcf,
    ) {}
}
