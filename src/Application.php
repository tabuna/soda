<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Illuminate\Container\Container;

final class Application extends Container
{
    public function runningUnitTests(): bool
    {
        return ($_ENV['env'] ?? '') === 'testing';
    }
}
