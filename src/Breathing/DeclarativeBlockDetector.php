<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

/**
 * Array literals, fluent chains, use/use const blocks are declarative — no penalty.
 *
 * @internal
 */
final class DeclarativeBlockDetector
{
    /**
     * @param list<string> $lines
     */
    public function isDeclarative(array $lines): bool
    {
        if ($lines === []) {
            return false;
        }

        $counts = $this->countPatterns($lines);
        $n = count(array_filter($lines, fn (string $l) => trim($l) !== ''));

        if ($n < 3) {
            return false;
        }

        return $this->meetsThreshold($counts, $n);
    }

    /**
     * @param list<string> $lines
     *
     * @return array{arrayLike: int, fluentLike: int, accessorLike: int, templateLike: int, instanceofLike: int, useLike: int}
     */
    private function countPatterns(array $lines): array
    {
        $counts = [
            'arrayLike'      => 0,
            'fluentLike'     => 0,
            'accessorLike'   => 0,
            'templateLike'   => 0,
            'instanceofLike' => 0,
            'useLike'        => 0,
        ];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            $this->accumulatePatterns($trimmed, $counts);
        }

        return $counts;
    }

    /**
     * @param array{arrayLike: int, fluentLike: int, accessorLike: int, templateLike: int, instanceofLike: int, useLike: int} $counts
     */
    private function accumulatePatterns(string $trimmed, array &$counts): void
    {
        $checks = [
            'arrayLike'      => fn () => $this->isArrayLike($trimmed),
            'fluentLike'     => fn () => str_contains($trimmed, '->'),
            'accessorLike'   => fn () => $this->isAccessorLike($trimmed),
            'templateLike'   => fn () => $this->isTemplateLike($trimmed),
            'instanceofLike' => fn () => str_contains($trimmed, ' instanceof '),
            'useLike'        => fn () => str_starts_with($trimmed, 'use ') || str_starts_with($trimmed, 'use const '),
        ];

        foreach ($checks as $key => $check) {
            if ($check()) {
                $counts[$key]++;
            }
        }
    }

    private function isArrayLike(string $line): bool
    {
        return str_ends_with($line, ',')
            || str_ends_with($line, '],')
            || str_ends_with($line, ');')
            || str_contains($line, ' => ');
    }

    private function isAccessorLike(string $line): bool
    {
        return str_contains($line, '$this->data[')
            || str_contains($line, 'return $this->');
    }

    private function isTemplateLike(string $line): bool
    {
        return str_contains($line, '%')
            && preg_match('/%\d*s|%\d*d|%\.\d*f/', $line);
    }

    /**
     * @param array{arrayLike: int, fluentLike: int, accessorLike: int, templateLike: int, instanceofLike: int, useLike: int} $counts
     */
    private function meetsThreshold(array $counts, int $n): bool
    {
        $threshold = (int) ceil((float) $n * 0.3);
        $accessorThreshold = (int) ceil((float) $n * 0.2);
        $templateThreshold = (int) ceil((float) $n * 0.25);
        $instanceofThreshold = (int) ceil((float) $n * 0.25);
        $useThreshold = (int) ceil((float) $n * 0.5);

        return $counts['arrayLike'] >= $threshold
            || $counts['fluentLike'] >= $threshold
            || $counts['accessorLike'] >= $accessorThreshold
            || $counts['templateLike'] >= $templateThreshold
            || $counts['instanceofLike'] >= $instanceofThreshold
            || ($counts['useLike'] ?? 0) >= $useThreshold;
    }
}
