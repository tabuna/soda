<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Bunnivo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    public function build(): Violation
    {
        $context = ViolationContext::create($this->limits, $this->method, $this->class);

        return new Violation($this->rule, $this->file, $context);
    }
}
