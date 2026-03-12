<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

use const T_ABSTRACT;
use const T_BOOLEAN_AND;
use const T_BOOLEAN_OR;
use const T_CONCAT_EQUAL;
use const T_CONSTANT_ENCAPSED_STRING;
use const T_DIV_EQUAL;
use const T_DNUMBER;
use const T_IS_GREATER_OR_EQUAL;
use const T_IS_SMALLER_OR_EQUAL;
use const T_LNUMBER;
use const T_MINUS_EQUAL;
use const T_MUL_EQUAL;
use const T_PLUS_EQUAL;
use const T_YIELD;

/**
 * @internal
 */
final class TokenKeywordDetector
{
    public static function isKeyword(int $id): bool
    {
        return $id >= T_ABSTRACT && $id <= T_YIELD;
    }

    public static function isLiteral(int $id): bool
    {
        return in_array($id, [T_CONSTANT_ENCAPSED_STRING, T_LNUMBER, T_DNUMBER], true);
    }

    public static function isOperator(int $id): bool
    {
        $ops = [
            T_IS_SMALLER_OR_EQUAL,
            T_IS_GREATER_OR_EQUAL,
            T_BOOLEAN_AND,
            T_BOOLEAN_OR,
            T_PLUS_EQUAL,
            T_MINUS_EQUAL,
            T_MUL_EQUAL,
            T_DIV_EQUAL,
            T_CONCAT_EQUAL,
        ];

        return in_array($id, $ops, true) || ($id >= 43 && $id <= 126);
    }
}
