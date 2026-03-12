<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Commands;

use Illuminate\Container\Container;

/**
 * Minimal container for Illuminate Console compatibility.
 * Adds runningUnitTests() required by ConfiguresPrompts.
 *
 * @psalm-suppress UnusedClass Used via Illuminate container binding
 */
final class SodaContainer extends Container
{
    public function runningUnitTests(): bool
    {
        return false;
    }
}
