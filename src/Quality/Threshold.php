<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

/**
 * @internal
 */
final readonly class Threshold
{
    public function __construct(
        private ?int $value,
        private int|float|null $limit,
    ) {}

    public static function empty(): self
    {
        return new self(null, null);
    }

    public function withValue(int $value): self
    {
        return new self($value, $this->limit);
    }

    public function withLimit(int|float $limit): self
    {
        return new self($this->value, $limit);
    }

    public function value(): ?int
    {
        return $this->value;
    }

    public function limit(): int|float|null
    {
        return $this->limit;
    }
}
