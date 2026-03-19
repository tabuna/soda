<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

final readonly class RuleScoring
{
    public function __construct(
        public int|float $defaultThreshold,
    ) {}
}
