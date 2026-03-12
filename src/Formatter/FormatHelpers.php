<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Formatter;

trait FormatHelpers
{
    private static function pct(int $part, int $total): float
    {
        return $total > 0 ? ((float) $part / (float) $total) * 100.0 : 0.0;
    }
}
