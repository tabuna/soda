<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing\Calc;

use Bunnivo\Soda\Breathing\BreathingFactors;
use Bunnivo\Soda\Breathing\CbsInput;
use Bunnivo\Soda\Breathing\LineBlockData;

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
