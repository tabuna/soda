<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

/**
 * @internal
 */
final class CbsCalculator
{
    public static function calculate(CbsInput $input): float
    {
        $divisor = self::divisor($input->totalLines());
        $sizeFactor = self::sizeFactor($input->totalLines());
        $c = $input->cognitive();
        $a = $input->airiness();

        $effectiveLcf = min($c->lcf, 4.0);
        $denominator = 1.0 + ($c->wcd * $effectiveLcf) / $divisor;
        $numerator = $a->vbi * $a->irs * $a->col;

        return min(1.0, ($numerator * $sizeFactor) / $denominator);
    }

    private static function divisor(int $totalLines): float
    {
        $totalF = (float) $totalLines;
        $divisor = 100.0 + 120.0 / (1.0 + $totalF / 25.0);

        if ($totalLines > 400) {
            return $divisor * 5.0;
        }
        if ($totalLines < 250 && $totalLines >= 50) {
            return $divisor * 2.9;
        }

        return $divisor;
    }

    private static function sizeFactor(int $totalLines): float
    {
        $totalF = (float) $totalLines;
        $sizeFactor = max(1.0, min(10.0, 2400.0 / ($totalF + 50.0)));

        if ($totalLines > 400) {
            return min(10.0, $sizeFactor * 2.0);
        }

        return $sizeFactor;
    }
}
