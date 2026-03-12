<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Structure;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;

/**
 * @internal
 */
final class ExpressionHandlers
{
    public static function handle(MetricsState $state, Node $node): bool
    {
        return match (true) {
            $node instanceof StaticCall          => self::incStaticCall($state),
            $node instanceof MethodCall          => self::incMethodCall($state),
            $node instanceof StaticPropertyFetch => self::incStaticProperty($state),
            $node instanceof PropertyFetch       => self::incPropertyFetch($state),
            default                              => false,
        };
    }

    private static function incStaticCall(MetricsState $state): bool
    {
        $state->inc('staticMethodCalls');

        return true;
    }

    private static function incMethodCall(MetricsState $state): bool
    {
        $state->inc('nonStaticMethodCalls');

        return true;
    }

    private static function incStaticProperty(MetricsState $state): bool
    {
        $state->inc('staticAttributeAccesses');

        return true;
    }

    private static function incPropertyFetch(MetricsState $state): bool
    {
        $state->inc('nonStaticAttributeAccesses');

        return true;
    }
}
