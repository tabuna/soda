<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Structure;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;

/**
 * @internal
 */
final class NodeHandlers
{
    private const array SUPER_GLOBALS = [
        '_ENV'     => true,
        '_POST'    => true,
        '_GET'     => true,
        '_COOKIE'  => true,
        '_SERVER'  => true,
        '_FILES'   => true,
        '_REQUEST' => true,
    ];

    public static function handleVariable(MetricsState $state, Variable $node): void
    {
        $name = $node->name;

        if ($name instanceof Node\Identifier) {
            $name = $name->toString();
        } elseif (! is_string($name)) {
            return;
        }

        if ($name === 'GLOBALS') {
            $state->inc('globalVariableAccesses');

            return;
        }

        if (isset(self::SUPER_GLOBALS[$name])) {
            $state->inc('superGlobalVariableAccesses');
        }
    }
}
