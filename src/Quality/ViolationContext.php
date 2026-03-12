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
