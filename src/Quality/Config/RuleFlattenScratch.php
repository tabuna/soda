<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

/**
 * Mutable accumulator while flattening nested `rules` from soda.json.
 *
 * @phpstan-type Thresholds array<string, int|float>
 */
final class RuleFlattenScratch
{
    /**
     * @var Thresholds
     */
    public array $thresholds = [];

    /**
     * @var list<string>
     */
    public array $disabled = [];
}
