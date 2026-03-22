<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing\Calc;

use Bunnivo\Soda\Breathing\LineBlockData;

/**
 * @internal
 */
final class ColCalculator
{
    public static function calculate(LineBlockData $data): float
    {
        $nLines = $data->nLines();
        if ($nLines <= 0) {
            return 0.0;
        }

        $declarativeBonus = DeclarativeBonusCalculator::calculate($data->blocks(), $data->blockLines());
        $raw = (float) ($data->nBlank() + $data->shortBlocks() + $declarativeBonus) / (float) $nLines;
        $raw += self::sizeModifier($data->totalLines());

        return min(1.0, max(0.0, $raw));
    }

    private static function sizeModifier(int $totalLines): float
    {
        if ($totalLines < 100 && $totalLines >= 50) {
            return 0.12;
        }

        if ($totalLines < 200 && $totalLines >= 100) {
            return 0.05;
        }

        return 0.0;
    }
}
