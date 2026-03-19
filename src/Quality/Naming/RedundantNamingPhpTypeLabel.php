<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Naming;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;

/**
 * @internal
 */
final class RedundantNamingPhpTypeLabel
{
    public static function fromTypeNode(Node $type): string
    {
        return match (true) {
            $type instanceof Name                                => $type->toString(),
            $type instanceof Identifier                          => $type->name,
            $type instanceof NullableType                        => self::fromTypeNode($type->type),
            $type instanceof UnionType && isset($type->types[0]) => self::fromTypeNode($type->types[0]),
            default                                              => '',
        };
    }
}
