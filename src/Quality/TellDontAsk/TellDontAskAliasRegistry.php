<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\TellDontAsk;

use function array_key_last;
use function array_pop;

/**
 * @internal
 */
final class TellDontAskAliasRegistry
{
    /**
     * @psalm-var list<array<string, list<array{receiver: string, method: string}>>>
     */
    private array $scopes = [];

    public function reset(): void
    {
        $this->scopes = [[]];
    }

    public function clear(): void
    {
        $this->scopes = [];
    }

    public function hasScopes(): bool
    {
        return $this->scopes !== [];
    }

    public function pushScope(): void
    {
        if ($this->scopes !== []) {
            $this->scopes[] = [];
        }
    }

    public function popScope(): void
    {
        if ($this->scopes !== []) {
            array_pop($this->scopes);
        }
    }

    /**
     * @return list<array<string, list<array{receiver: string, method: string}>>>
     */
    public function all(): array
    {
        return $this->scopes;
    }

    /**
     * @param list<array{receiver: string, method: string}> $questions
     */
    public function record(string $variable, array $questions): void
    {
        $scopeIndex = array_key_last($this->scopes);

        if ($scopeIndex === null) {
            return;
        }

        if ($questions === []) {
            unset($this->scopes[$scopeIndex][$variable]);

            return;
        }

        $this->scopes[$scopeIndex][$variable] = $questions;
    }
}
