<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing\Calc;

use Bunnivo\Soda\Breathing\LcfVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;

/**
 * @internal
 */
final class BreathingLcfFromAstResolver
{
    /**
     * @param array<array-key, Node>|null $astNodes
     */
    public static function resolve(?array $astNodes): float
    {
        if ($astNodes === null) {
            return 1.0;
        }

        $visitor = new LcfVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse(array_values($astNodes));

        return $visitor->lcf();
    }
}
