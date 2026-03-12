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
