<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Flow;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;

/**
 * @internal
 */
final class ConditionExtractor
{
    public static function extract(Node $node): ?Expr
    {
        return match (true) {
            $node instanceof If_          => $node->cond,
            $node instanceof ElseIf_      => $node->cond,
            $node instanceof While_       => $node->cond,
            $node instanceof Do_          => $node->cond,
            $node instanceof For_         => $node->cond[0] ?? null,
            $node instanceof Expr\Ternary => $node->cond,
            default                       => null,
        };
    }
}
