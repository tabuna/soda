<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

use function array_key_exists;
use function is_array;

use const T_AND_EQUAL;
use const T_BOOLEAN_AND;
use const T_BOOLEAN_OR;
use const T_CLOSE_TAG;
use const T_COALESCE_EQUAL;
use const T_COMMENT;
use const T_CONCAT_EQUAL;
use const T_CONSTANT_ENCAPSED_STRING;
use const T_DECLARE;
use const T_DIV_EQUAL;
use const T_DNUMBER;
use const T_DOC_COMMENT;
use const T_DOUBLE_COLON;
use const T_ENCAPSED_AND_WHITESPACE;
use const T_IS_EQUAL;
use const T_IS_IDENTICAL;
use const T_IS_NOT_EQUAL;
use const T_IS_NOT_IDENTICAL;
use const T_LNUMBER;
use const T_LOGICAL_AND;
use const T_LOGICAL_OR;
use const T_LOGICAL_XOR;
use const T_MINUS_EQUAL;
use const T_MOD_EQUAL;
use const T_MUL_EQUAL;
use const T_NAMESPACE;
use const T_NULLSAFE_OBJECT_OPERATOR;
use const T_OBJECT_OPERATOR;
use const T_OPEN_TAG;
use const T_OPEN_TAG_WITH_ECHO;
use const T_OR_EQUAL;
use const T_PLUS_EQUAL;
use const T_POW_EQUAL;
use const T_SL_EQUAL;
use const T_SPACESHIP;
use const T_SR_EQUAL;
use const T_STRING;
use const T_USE;
use const T_VARIABLE;
use const T_WHITESPACE;
use const T_XOR_EQUAL;

/**
 * @internal
 */
final class TokenWeightResolver
{
    private const float BOILERPLATE = 0.0;

    private const float DELIMITER = 0.5;

    private const float LITERAL = 0.8;

    private const float IDENTIFIER = 1.0;

    private const float KEYWORD = 1.2;

    private const float OPERATOR = 1.5;

    private const array WEIGHTS = [
        T_USE                      => self::BOILERPLATE,
        T_NAMESPACE                => self::BOILERPLATE,
        T_DECLARE                  => self::BOILERPLATE,
        T_STRING                   => 1.0,
        T_VARIABLE                 => 1.0,
        T_CONSTANT_ENCAPSED_STRING => 0.8,
        T_LNUMBER                  => 0.8,
        T_DNUMBER                  => 0.8,
        T_ENCAPSED_AND_WHITESPACE  => 0.8,
        T_AND_EQUAL                => 2.0,
        T_OR_EQUAL                 => 2.0,
        T_BOOLEAN_AND              => 2.0,
        T_BOOLEAN_OR               => 2.0,
        T_LOGICAL_AND              => 2.0,
        T_LOGICAL_OR               => 2.0,
        T_LOGICAL_XOR              => 2.0,
        T_IS_EQUAL                 => 1.5,
        T_IS_NOT_EQUAL             => 1.5,
        T_IS_IDENTICAL             => 1.5,
        T_IS_NOT_IDENTICAL         => 1.5,
        T_SPACESHIP                => 1.5,
        T_PLUS_EQUAL               => 1.5,
        T_MINUS_EQUAL              => 1.5,
        T_MUL_EQUAL                => 1.5,
        T_DIV_EQUAL                => 1.5,
        T_CONCAT_EQUAL             => 1.5,
        T_MOD_EQUAL                => 1.5,
        T_POW_EQUAL                => 1.5,
        T_SL_EQUAL                 => 1.5,
        T_SR_EQUAL                 => 1.5,
        T_XOR_EQUAL                => 1.5,
        T_COALESCE_EQUAL           => 1.5,
        T_NULLSAFE_OBJECT_OPERATOR => 1.3,
        T_OBJECT_OPERATOR          => 1.3,
        T_DOUBLE_COLON             => 1.3,
    ];

    /**
     * @param string|array{0: int, 1: string, 2: int} $token
     */
    public function weight(string|array $token): float
    {
        if (! is_array($token)) {
            return self::OPERATOR;
        }

        return $this->weightForToken($token[0], $token[1]);

    }

    private function weightForToken(int $id, string $text): float
    {

        return array_key_exists($id, self::WEIGHTS)
            ? self::WEIGHTS[$id]
            : $this->resolveWeight($id, $text);
    }

    private function resolveWeight(int $id, string $text): float
    {
        $w = $this->weightForDelimiter($id)
            ?? $this->weightForKeywordOrLiteral($id)
            ?? $this->weightForIdentifier($id, $text)
            ?? $this->weightForOperator($id);

        return $w ?? self::DELIMITER;
    }

    private function weightForDelimiter(int $id): ?float
    {
        if (in_array($id, [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG], true)) {
            return self::DELIMITER;
        }

        if (in_array($id, [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            return 0.0;
        }

        return null;
    }

    private function weightForKeywordOrLiteral(int $id): ?float
    {
        if (TokenKeywordDetector::isKeyword($id)) {
            return self::KEYWORD;
        }

        if (TokenKeywordDetector::isLiteral($id)) {
            return self::LITERAL;
        }

        return null;
    }

    private function weightForIdentifier(int $id, string $text): ?float
    {
        if ($id === T_STRING && $text !== '') {
            return self::IDENTIFIER;
        }

        if ($id === T_VARIABLE) {
            return self::IDENTIFIER;
        }

        return null;
    }

    private function weightForOperator(int $id): ?float
    {
        if (TokenKeywordDetector::isOperator($id)) {
            return self::OPERATOR;
        }

        return null;
    }
}
