<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

final readonly class ViolationContext
{
    public function __construct(
        public ?string $method,
        public ?string $class,
        public Limits $limits,
    ) {}

    public static function create(Limits $limits, ?string $method, ?string $class): self
    {
        return new self($method, $class, $limits);
    }

    public function value(): int
    {
        return $this->limits->value;
    }

    public function threshold(): int
    {
        return $this->limits->threshold;
    }
}
