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
    public static function handle(MetricsState $state, Node $node): void
    {
        match (true) {
            $node instanceof StaticCall          => $state->inc('staticMethodCalls'),
            $node instanceof MethodCall          => $state->inc('nonStaticMethodCalls'),
            $node instanceof StaticPropertyFetch => $state->inc('staticAttributeAccesses'),
            $node instanceof PropertyFetch       => $state->inc('nonStaticAttributeAccesses'),
            default                              => null,
        };
    }
}
