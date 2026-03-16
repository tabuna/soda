<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use function array_key_last;
use function array_pop;

/**
 * @internal
 *
 * @psalm-var array<string, array{depth: int, line: int}>
 */
final class NestingTracker
{
    private array $nestingByMethod = [];

    /**
     * @psalm-var list<int>
     */
    private array $depthStack = [];

    private int $depth = 0;

    private bool $tracking = true;

    public function startMethod(string $name, int $line): void
    {
        $this->depthStack[] = $this->depth;
        $this->depth = 0;
        $this->nestingByMethod[$name] = ['depth' => 0, 'line' => $line];
    }

    public function endMethod(): void
    {
        $this->depth = array_pop($this->depthStack);
    }

    public function enterClosure(): void
    {
        $this->depthStack[] = $this->depth;
        $this->depth = 0;
        $this->tracking = false;
    }

    public function leaveClosure(): void
    {
        $this->depth = array_pop($this->depthStack);
        $this->tracking = true;
    }

    public function pushControl(int $line): void
    {
        $this->depth++;
        if ($this->tracking) {
            $this->updateMax($line);
        }
    }

    public function popControl(): void
    {
        $this->depth--;
    }

    /**
     * @psalm-return array<string, array{depth: int, line: int}>
     */
    public function result(): array
    {
        return $this->nestingByMethod;
    }

    private function updateMax(int $line): void
    {
        $last = array_key_last($this->nestingByMethod);
        if ($last === null) {
            return;
        }

        $current = $this->nestingByMethod[$last];
        if ($this->depth > $current['depth']) {
            $this->nestingByMethod[$last] = ['depth' => $this->depth, 'line' => $line];
        }
    }
}
