<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EfferentCoupling;

use function array_pop;
use function collect;
use function count;
use function strcasecmp;

/**
 * Mutable Ce state: dependency edges and class/trait stack.
 *
 * @internal
 */
final class EfferentCouplingGraph
{
    /**
     * @var array<string, array<string, true>>
     */
    private array $deps = [];

    /**
     * @var list<array{name: non-empty-string, extends: ?non-empty-string}>
     */
    private array $frames = [];

    /**
     * @psalm-param non-empty-string $name
     * @psalm-param non-empty-string|null $extends
     */
    public function pushFrame(string $name, ?string $extends): void
    {
        $this->frames[] = ['name' => $name, 'extends' => $extends];
        $this->deps[$name] ??= [];
    }

    public function popFrame(): void
    {
        if ($this->frames !== []) {
            array_pop($this->frames);
        }
    }

    /**
     * @psalm-return non-empty-string|null
     */
    public function currentOwner(): ?string
    {
        if ($this->frames === []) {
            return null;
        }

        $frame = collect($this->frames)->last();

        return $frame !== null ? $frame['name'] : null;
    }

    /**
     * @psalm-return non-empty-string|null
     */
    public function currentExtends(): ?string
    {
        if ($this->frames === []) {
            return null;
        }

        $frame = collect($this->frames)->last();

        return $frame !== null ? $frame['extends'] : null;
    }

    /**
     * @psalm-param non-empty-string $fqcn
     */
    public function addEdge(string $fqcn): void
    {
        $owner = $this->currentOwner();

        if ($owner === null) {
            return;
        }

        if (strcasecmp($fqcn, $owner) === 0) {
            return;
        }

        $edges = $this->deps[$owner] ?? [];
        $edges[$fqcn] = true;
        $this->deps[$owner] = $edges;
    }

    /**
     * @psalm-return array<string, int>
     */
    public function couplingCountsByClass(): array
    {
        $out = [];

        foreach ($this->deps as $class => $set) {
            $out[$class] = count($set);
        }

        return $out;
    }
}
