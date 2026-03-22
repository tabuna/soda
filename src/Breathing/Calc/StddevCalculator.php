<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing\Calc;

use function count;

/**
 * @internal
 *
 * @param list<int|float> $values
 */
final class StddevCalculator
{
    /**
     * @param list<int|float> $values
     */
    public static function calculate(array $values): float
    {
        $n = count($values);

        if ($n < 2) {
            return 0.0;
        }

        $mean = array_sum($values) / (float) $n;
        $variance = 0.0;

        foreach ($values as $v) {
            $variance += ((float) $v - $mean) ** 2.0;
        }

        return ($variance / (float) ($n - 1)) ** 0.5;
    }
}
