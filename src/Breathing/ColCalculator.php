<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

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

        return min(0.65, $raw);
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
