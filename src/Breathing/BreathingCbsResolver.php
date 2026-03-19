<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

/**
 * @internal
 */
final class BreathingCbsResolver
{
    public static function resolve(BreathingFactors $factors, LineBlockData $lineBlock): float
    {
        $input = CbsInput::fromFactors(
            $factors->cognitive(),
            $factors->airiness(),
            $lineBlock->totalLines(),
        );

        return CbsCalculator::calculate($input);
    }
}
