<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

/**
 * @internal
 *
 * @psalm-return array{irs: float, vbi: float, col: float}
 */
final class BreathingPerceptualIndices
{
    /**
     * @param list<string|array{0: int, 1: string, 2: int}> $tokens
     */
    public static function collect(array $tokens, LineBlockData $lineBlock): array
    {
        return [
            'irs' => IrsCalculator::calculate($tokens),
            'vbi' => VbiCalculator::calculate($lineBlock),
            'col' => ColCalculator::calculate($lineBlock),
        ];
    }
}
