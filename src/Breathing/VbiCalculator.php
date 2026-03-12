<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

/**
 * @internal
 */
final class VbiCalculator
{
    public static function calculate(LineBlockData $data): float
    {
        $nLines = $data->nLines();
        $blocks = $data->blocks();

        if ($nLines <= 0 || $blocks === []) {
            return 0.0;
        }

        $ratio = (float) $data->nBlank() / (float) $nLines;
        /** @var list<int> $blocksList */
        $blocksList = $blocks;
        $blockFactor = self::blockFactor($data, $blocksList);

        return $ratio * max(0.0, $blockFactor);
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
