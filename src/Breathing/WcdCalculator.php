<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

use function is_array;
use function strlen;

/**
 * @internal
 *
 * @param list<string|array{0: int, 1: string, 2: int}> $tokens
 */
final class WcdCalculator
{
    /**
     * @param list<string|array{0: int, 1: string, 2: int}> $tokens
     */
    public static function calculate(array $tokens, int $nLines, TokenWeightResolver $resolver): float
    {
        if ($nLines <= 0) {
            return 0.0;
        }

        $weightedSum = 0.0;

        foreach ($tokens as $token) {
            $text = is_array($token) ? $token[1] : $token;
            $weightedSum += (float) strlen($text) * $resolver->weight($token);
        }

        return $weightedSum / (float) $nLines;
    }
}
