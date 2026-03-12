<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

/**
 * @internal
 */
final readonly class RuleContext
{
    public function __construct(
        private ?string $class,
        private ?string $method,
        private ?int $line,
    ) {}

    public static function empty(): self
    {
        return new self(null, null, null);
    }

    public function withClass(?string $class): self
    {
        return new self($class, $this->method, $this->line);
    }

    public function withMethod(?string $method): self
    {
        return new self($this->class, $method, $this->line);
    }

    public function withLine(?int $line): self
    {
        return new self($this->class, $this->method, $line);
    }

    public function class(): ?string
    {
        return $this->class;
    }

    public function method(): ?string
    {
        return $this->method;
    }
}
