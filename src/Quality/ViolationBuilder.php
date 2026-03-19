<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

/**
 * Fluent builder for Violation.
 *
 * @example
 *   ViolationBuilder::of('max_methods_per_class', $file, new Limits($value, $max))
 *       ->atClass($className)
 *       ->build();
 */
final class ViolationBuilder
{
    private ?string $method = null;

    private ?string $class = null;

    private ?int $line = null;

    private ?string $message = null;

    private function __construct(
        private readonly string $rule,
        private readonly string $file,
        private readonly Limits $limits,
    ) {}

    public static function of(string $rule, string $file, Limits $limits): self
    {
        return new self($rule, $file, $limits);
    }

    public function atMethod(?string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function atClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function atLine(?int $line): self
    {
        $this->line = $line;

        return $this;
    }

    public function withMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function build(): Violation
    {
        $location = new ViolationLocation($this->method, $this->class, $this->line);
        $context = ViolationContext::create($this->limits, $location, $this->message);

        return new Violation($this->rule, $this->file, $context);
    }
}
