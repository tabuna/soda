<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Naming;

use function collect;

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
            $type instanceof Name         => $type->toString(),
            $type instanceof Identifier   => $type->name,
            $type instanceof NullableType => self::fromTypeNode($type->type),
            $type instanceof UnionType    => self::firstUnionMember($type),
            default                       => '',
        };
    }

    private static function firstUnionMember(UnionType $type): string
    {
        $member = collect($type->types)->first();

        return $member !== null ? self::fromTypeNode($member) : '';
    }
}
