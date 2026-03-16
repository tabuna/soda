<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

final readonly class ViolationContext
{
    public function __construct(
        public Limits $limits,
        public ViolationLocation $location = new ViolationLocation(),
    ) {}

    public static function create(Limits $limits, ViolationLocation $location = new ViolationLocation()): self
    {
        return new self($limits, $location);
    }

    public function method(): ?string
    {
        return $this->location->method;
    }

    public function class(): ?string
    {
        return $this->location->class;
    }

    public function line(): ?int
    {
        return $this->location->line;
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
