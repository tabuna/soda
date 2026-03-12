<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

use function count;
use function token_get_all;

use const T_COMMENT;
use const T_DOC_COMMENT;
use const T_WHITESPACE;

/**
 * @internal
 *
 * @return array{blockLines: list<list<string>>, blocks: list<int>, nBlank: int, nLines: int, shortBlocks: int, totalLines: int}
 */
final class LineAnalyser
{
    public static function analyse(string $source): array
    {
        $lines = explode("\n", $source);
        $commentOnlyLines = self::commentOnlyLineNumbers($source);
        $nBlank = 0;
        $blocks = [];
        $blockLines = [];
        $currentBlock = 0;
        $currentLines = [];

        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);
            $lineNo = $lineNum + 1;
            $isBlank = $trimmed === '' || isset($commentOnlyLines[$lineNo]);

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

        $nLines = 0;
        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);
            $lineNo = $lineNum + 1;
            if ($trimmed !== '' && ! isset($commentOnlyLines[$lineNo])) {
                $nLines++;
            }
        }
        $shortBlocks = count(array_filter($blocks, fn (int $b) => $b <= 3));
        $totalLines = count($lines);

        return [
            'nBlank'      => $nBlank,
            'nLines'      => $nLines,
            'totalLines'  => $totalLines,
            'blocks'      => $blocks,
            'blockLines'  => $blockLines,
            'shortBlocks' => $shortBlocks,
        ];
    }

    /**
     * @return array<int, true> line numbers that contain only comments/whitespace
     */
    private static function commentOnlyLineNumbers(string $source): array
    {
        $tokens = @token_get_all($source);
        $linesWithCode = [];

        foreach ($tokens as $token) {
            if (! is_array($token)) {
                continue;
            }
            $line = $token[2];
            $content = $token[1];
            $lastLine = $line + substr_count($content, "\n");
            $isCommentOrWhitespace = in_array(
                $token[0],
                [T_COMMENT, T_DOC_COMMENT, T_WHITESPACE],
                true,
            );
            if (! $isCommentOrWhitespace) {
                for ($l = $line; $l <= $lastLine; $l++) {
                    $linesWithCode[$l] = true;
                }
            }
        }

        $commentOnly = [];
        $totalLines = substr_count($source, "\n") + 1;
        for ($l = 1; $l <= $totalLines; $l++) {
            if (! isset($linesWithCode[$l])) {
                $commentOnly[$l] = true;
            }
        }

        return $commentOnly;
    }
}
