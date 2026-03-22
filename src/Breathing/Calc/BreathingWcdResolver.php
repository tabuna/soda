<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing\Calc;

use Bunnivo\Soda\Breathing\LineBlockData;
use Bunnivo\Soda\Breathing\TokenWeightResolver;

/**
 * @internal
 */
final class BreathingWcdResolver
{
    /**
     * @param list<string|array{0: int, 1: string, 2: int}> $tokens
     */
    public static function resolve(array $tokens, LineBlockData $lineBlock): float
    {
        $resolver = new TokenWeightResolver();

        return WcdCalculator::calculate($tokens, $lineBlock->nLines(), $resolver);
    }
}
