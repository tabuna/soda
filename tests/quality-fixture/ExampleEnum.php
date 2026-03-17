<?php

declare(strict_types=1);

namespace Test;

/**
 * Fixture for enum-workaround tests. REMOVE_WHEN sebastian/complexity adds Enum support.
 */
enum ExampleEnum: string
{
    case Foo = 'foo';
    case Bar = 'bar';

    public function label(): string
    {
        return match ($this) {
            self::Foo => 'Foo',
            self::Bar => 'Bar',
        };
    }
}
