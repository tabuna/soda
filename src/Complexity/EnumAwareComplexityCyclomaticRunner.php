<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Complexity;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use SebastianBergmann\Complexity\CyclomaticComplexityCalculatingVisitor;

/**
 * @internal
 */
final class EnumAwareComplexityCyclomaticRunner
{
    /**
     * @param Stmt[] $statements
     *
     * @return positive-int
     */
    public static function fromStatements(array $statements): int
    {
        $traverser = new NodeTraverser();
        $visitor = new CyclomaticComplexityCalculatingVisitor();
        $traverser->addVisitor($visitor);
        $traverser->traverse($statements);

        return $visitor->cyclomaticComplexity();
    }
}
