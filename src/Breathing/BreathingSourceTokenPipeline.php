<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

use function token_get_all;

/**
 * @internal
 *
 * @psalm-return array{tokens: list<string|array{0: int, 1: string, 2: int}>, lineBlock: LineBlockData}
 */
final class BreathingSourceTokenPipeline
{
    public static function prepare(string $source): array
    {
        $tokens = token_get_all($source);
        $lineData = LineAnalyser::analyse($source);

        return [
            'tokens'    => $tokens,
            'lineBlock' => new LineBlockData($lineData),
        ];
    }
}
