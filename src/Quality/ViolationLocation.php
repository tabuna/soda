<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

final readonly class ViolationLocation
{
    public function __construct(
        public ?string $method = null,
        public ?string $class = null,
        public ?int $line = null,
    ) {}
}
