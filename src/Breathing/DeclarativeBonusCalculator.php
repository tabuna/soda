<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

/**
 * @internal
 *
 * @param list<int>          $blocks
 * @param list<list<string>> $blockLines
 */
final class DeclarativeBonusCalculator
{
    /**
     * @param list<int>          $blocks
     * @param list<list<string>> $blockLines
     */
    public static function calculate(array $blocks, array $blockLines): int
    {
        $declarativeBonus = 0;
        $maxBlock = $blocks !== [] ? max($blocks) : 0;
        $detector = new DeclarativeBlockDetector();

        foreach ($blocks as $i => $size) {
            if ($size < 4) {
                continue;
            }

            if (! isset($blockLines[$i])) {
                continue;
            }

            if (! $detector->isDeclarative($blockLines[$i])) {
                continue;
            }

            $bonus = $size === $maxBlock ? min($size - 3, 30) : min($size - 3, 6);
            $declarativeBonus += $bonus;
        }

        return $declarativeBonus;
    }
}
