<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing\Calc;

use Bunnivo\Soda\Breathing\DeclarativeBlockDetector;
use Bunnivo\Soda\Breathing\LineBlockData;

/**
 * @internal
 */
final class VbiCalculator
{
    private const float IDEAL_BLANK_RATIO = 0.5;

    private const int MAX_BLOCK_FOR_UNIFORM = 2;

    public static function calculate(LineBlockData $data): float
    {
        $nLines = $data->nLines();
        $blocks = $data->blocks();

        if ($nLines <= 0 || $blocks === []) {
            return 0.0;
        }

        $rawRatio = (float) $data->nBlank() / (float) $nLines;
        /** @var list<int> $blocks */
        $blockFactor = self::blockFactor($data, $blocks);

        $ratioComponent = min(1.0, $rawRatio / self::IDEAL_BLANK_RATIO);

        return min(1.0, max(0.0, $ratioComponent * max(0.0, $blockFactor)));
    }

    /**
     * @param list<int> $blocks
     */
    private static function blockFactor(LineBlockData $data, array $blocks): float
    {
        $maxBlock = $blocks !== [] ? max($blocks) : 0;
        $sigma = StddevCalculator::calculate($blocks);
        $blockFactor = $maxBlock > 0 ? 1.0 - ($sigma / (float) $maxBlock) : 1.0;

        if ($maxBlock > 0 && self::isMaxBlockDeclarative($data, $blocks, $maxBlock)) {
            return 1.0;
        }

        if ($maxBlock <= self::MAX_BLOCK_FOR_UNIFORM) {
            return 1.0;
        }

        return $blockFactor;
    }

    /**
     * @param list<int> $blocks
     */
    private static function isMaxBlockDeclarative(LineBlockData $data, array $blocks, int $maxBlock): bool
    {
        $maxBlockIndex = array_search($maxBlock, $blocks, true);
        if ($maxBlockIndex === false || ! isset($data->blockLines()[$maxBlockIndex])) {
            return false;
        }

        return (new DeclarativeBlockDetector())->isDeclarative($data->blockLines()[$maxBlockIndex]);
    }
}
