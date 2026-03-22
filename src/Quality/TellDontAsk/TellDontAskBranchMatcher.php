<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\TellDontAsk;

use function explode;
use function str_contains;

/**
 * @internal
 */
final class TellDontAskBranchMatcher
{
    public static function currentClassName(?string $methodName): ?string
    {
        return $methodName !== null && str_contains($methodName, '::')
            ? explode('::', $methodName, 2)[0]
            : null;
    }

    /**
     * @param list<array{receiver: string, method: string}> $questions
     * @param array{receiver: string, method: string}       $command
     *
     * @return array{receiver: string, method: string}|null
     */
    public static function firstMatch(array $questions, array $command): ?array
    {
        foreach ($questions as $question) {
            if ($question['receiver'] !== $command['receiver']) {
                continue;
            }

            if ($question['method'] === $command['method']) {
                continue;
            }

            return $question;
        }

        return null;
    }
}
