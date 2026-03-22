<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Support;

use function preg_match;
use function preg_replace;
use function str_replace;
use function strlen;
use function substr_count;

use const T_COMMENT;
use const T_DOC_COMMENT;

use function trim;

/**
 * Extracts comment-based code smells from source without reparsing the file.
 */
final class SourceCommentIssueScanner
{
    /**
     * @return array{
     *   todoFixme: list<array{line: int, kind: string, text: string}>,
     *   commentedCode: list<array{line: int, text: string}>
     * }
     */
    public static function scan(string $source): array
    {
        $issues = [
            'todoFixme'     => [],
            'commentedCode' => [],
        ];

        $tokens = @token_get_all($source);
        if (! is_array($tokens)) {
            return $issues;
        }

        foreach ($tokens as $token) {
            self::scanToken($token, $issues);
        }

        return $issues;
    }

    /**
     * @return list<array{line: int, text: string}>
     */
    private static function commentLines(string $comment, int $startLine): array
    {
        $lines = [];
        $parts = preg_split("/\r\n|\n|\r/", $comment);

        if ($parts === false) {
            return $lines;
        }

        foreach ($parts as $offset => $lineText) {
            $lines[] = [
                'line' => $startLine + $offset,
                'text' => $lineText,
            ];
        }

        if ($parts === []) {
            $lines[] = [
                'line' => $startLine,
                'text' => $comment,
            ];
        }

        return $lines;
    }

    private static function normalizeLine(string $line): string
    {
        $clean = str_replace("\t", ' ', $line);
        $clean = preg_replace('/^\s*(?:\/\*\*?|\/\/+|#|\*\/?|\*)\s?/', '', $clean) ?? $clean;
        $clean = preg_replace('/\s*\*\/\s*$/', '', $clean) ?? $clean;
        $clean = trim($clean);

        if ($clean === '' || self::isCommentDelimiterOnly($clean)) {
            return '';
        }

        return $clean;
    }

    /**
     * @param array{
     *   todoFixme: list<array{line: int, kind: string, text: string}>,
     *   commentedCode: list<array{line: int, text: string}>
     * } $issues
     */
    private static function scanToken(mixed $token, array &$issues): void
    {
        if (! is_array($token)) {
            return;
        }

        [$id, $text, $line] = $token;

        if ($id !== T_COMMENT && $id !== T_DOC_COMMENT) {
            return;
        }

        foreach (self::commentLines($text, $line) as $commentLine) {
            self::scanCommentLine($id, $commentLine, $issues);
        }
    }

    /**
     * @param array{line: int, text: string} $commentLine
     * @param array{
     *   todoFixme: list<array{line: int, kind: string, text: string}>,
     *   commentedCode: list<array{line: int, text: string}>
     * } $issues
     */
    private static function scanCommentLine(int $commentToken, array $commentLine, array &$issues): void
    {
        $normalized = self::normalizeLine($commentLine['text']);

        if ($normalized === '') {
            return;
        }

        self::appendTodoIssue($normalized, $commentLine['line'], $issues);

        if ($commentToken === T_DOC_COMMENT || ! CommentedCodeDetector::isCommentedCode($normalized)) {
            return;
        }

        $issues['commentedCode'][] = [
            'line' => $commentLine['line'],
            'text' => $normalized,
        ];
    }

    /**
     * @param array{
     *   todoFixme: list<array{line: int, kind: string, text: string}>,
     *   commentedCode: list<array{line: int, text: string}>
     * } $issues
     */
    private static function appendTodoIssue(string $normalized, int $line, array &$issues): void
    {
        if (preg_match('/\b(TODO|FIXME)\b/i', $normalized, $matches) !== 1) {
            return;
        }

        $issues['todoFixme'][] = [
            'line' => $line,
            'kind' => strtoupper($matches[1]),
            'text' => $normalized,
        ];
    }

    private static function isCommentDelimiterOnly(string $line): bool
    {
        return strlen($line) <= 2 && substr_count($line, '*') === strlen($line);
    }
}
