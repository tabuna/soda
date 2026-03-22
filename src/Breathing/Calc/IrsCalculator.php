<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing\Calc;

use Bunnivo\Soda\Breathing\TokenKeywordDetector;

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
    private const float IDEAL_AVG_LENGTH = 14.0;

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
                $identifiers = array_merge($identifiers, self::segmentLengths($text));

                continue;
            }

            if (self::isSimpleIdentifier($id, $text)) {
                $identifiers[] = strlen($text);
            }
        }

        if ($identifiers === []) {
            return 1.0;
        }

        $avg = array_sum($identifiers) / count($identifiers);

        return max(0.0, min(1.0, 1.0 - ((float) $avg - self::IDEAL_AVG_LENGTH) / 20.0));
    }

    /**
     * @return list<int>
     */
    private static function segmentLengths(string $qualified): array
    {
        $lengths = [];

        foreach (explode('\\', trim($qualified, '\\')) as $segment) {
            if (strlen($segment) >= 2) {
                $lengths[] = strlen($segment);
            }
        }

        return $lengths;
    }

    private static function isSimpleIdentifier(int $id, string $text): bool
    {
        if ($id !== T_STRING && $id !== T_VARIABLE) {
            return false;
        }

        if (strlen($text) < 2) {
            return false;
        }

        return ! TokenKeywordDetector::isKeyword($id);
    }
}
