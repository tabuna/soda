<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

use function count;

/**
 * @internal
 *
 * @return array{nBlank: int, nLines: int, totalLines: int, blocks: list<int>, blockLines: list<list<string>>, shortBlocks: int}
 */
final class LineAnalyser
{
    public static function analyse(string $source): array
    {
        $lines = explode("\n", $source);
        $commentOnly = CommentOnlyDetector::detect($source);

        [$blocks, $blockLines, $nBlank] = self::buildBlocks($lines, $commentOnly);
        $nLines = self::countCodeLines($lines, $commentOnly);
        $shortBlocks = count(array_filter($blocks, fn (int $b) => $b <= 3));

        return [
            'nBlank'      => $nBlank,
            'nLines'      => $nLines,
            'totalLines'  => count($lines),
            'blocks'      => $blocks,
            'blockLines'  => $blockLines,
            'shortBlocks' => $shortBlocks,
        ];
    }

    /**
     * @param list<string>     $lines
     * @param array<int, true> $commentOnly
     *
     * @return array{list<int>, list<list<string>>, int}
     */
    private static function buildBlocks(array $lines, array $commentOnly): array
    {
        $blocks = [];
        $blockLines = [];
        $currentBlock = 0;
        $currentLines = [];
        $nBlank = 0;

        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);
            $lineNo = $lineNum + 1;
            $isBlank = $trimmed === '' || isset($commentOnly[$lineNo]);

            if ($isBlank) {
                $nBlank++;
                if ($currentBlock > 0) {
                    $blocks[] = $currentBlock;
                    $blockLines[] = $currentLines;
                    $currentBlock = 0;
                    $currentLines = [];
                }
            } else {
                $currentBlock++;
                $currentLines[] = $trimmed;
            }
        }

        if ($currentBlock > 0) {
            $blocks[] = $currentBlock;
            $blockLines[] = $currentLines;
        }

        return [$blocks, $blockLines, $nBlank];
    }

    /**
     * @param list<string>     $lines
     * @param array<int, true> $commentOnly
     */
    private static function countCodeLines(array $lines, array $commentOnly): int
    {
        $nLines = 0;
        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);
            $lineNo = $lineNum + 1;
            if ($trimmed !== '' && ! isset($commentOnly[$lineNo])) {
                $nLines++;
            }
        }

        return $nLines;
    }
}
