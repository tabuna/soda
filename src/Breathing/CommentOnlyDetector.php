<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

use const T_COMMENT;
use const T_DOC_COMMENT;
use const T_WHITESPACE;

/**
 * @internal
 *
 * @return array<int, true> line numbers that contain only comments/whitespace
 */
final class CommentOnlyDetector
{
    public static function detect(string $source): array
    {
        $linesWithCode = self::linesWithCode($source);

        $totalLines = substr_count($source, "\n") + 1;

        $commentOnly = [];

        for ($l = 1; $l <= $totalLines; $l++) {
            if (! isset($linesWithCode[$l])) {
                $commentOnly[$l] = true;
            }
        }

        return $commentOnly;
    }

    /**
     * @return array<int, true>
     */
    private static function linesWithCode(string $source): array
    {
        $tokens = @token_get_all($source);

        $linesWithCode = [];

        foreach ($tokens as $token) {
            if (! is_array($token)) {
                continue;
            }

            if (self::isCodeToken($token[0])) {
                $line = $token[2];
                $lastLine = $line + substr_count($token[1], "\n");

                for ($l = $line; $l <= $lastLine; $l++) {
                    $linesWithCode[$l] = true;
                }
            }
        }

        return $linesWithCode;
    }

    private static function isCodeToken(int $id): bool
    {
        return ! in_array($id, [T_COMMENT, T_DOC_COMMENT, T_WHITESPACE], true);
    }
}
