<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

use function is_array;
use function strlen;

use const T_NAME_FULLY_QUALIFIED;
use const T_NAME_QUALIFIED;
use const T_STRING;
use const T_VARIABLE;

/**
 * @internal
 *
 * @param list<string|array{0: int, 1: string, 2: int}> $tokens
 */
final class IrsCalculator
{
    /**
     * @param list<string|array{0: int, 1: string, 2: int}> $tokens
     */
    public static function calculate(array $tokens): float
    {
        $identifiers = [];
        foreach ($tokens as $token) {
            if (! is_array($token)) {
                continue;
            }
            $id = $token[0];
            $text = $token[1];
            if ($id === T_VARIABLE) {
                $text = ltrim($text, '$');
            }
            if ($id === T_NAME_QUALIFIED || $id === T_NAME_FULLY_QUALIFIED) {
                foreach (explode('\\', trim($text, '\\')) as $segment) {
                    if (strlen($segment) >= 2) {
                        $identifiers[] = strlen($segment);
                    }
                }
                continue;
            }
            if (($id === T_STRING || $id === T_VARIABLE) && strlen($text) >= 2 && ! TokenKeywordDetector::isKeyword($id)) {
                $identifiers[] = strlen($text);
            }
        }

        if ($identifiers === []) {
            return 1.0;
        }

        $avg = array_sum($identifiers) / count($identifiers);

        return max(0.0, min(1.0, 1.0 - ((float) $avg - 8.0) / 20.0));
    }
}
