<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\While_;

/**
 * @internal
 */
final class ControlStructureMatcher
{
    private const array CONTROL_NODES = [
        If_::class,
        ElseIf_::class,
        Else_::class,
        For_::class,
        Foreach_::class,
        While_::class,
        Do_::class,
        Switch_::class,
        TryCatch::class,
        Catch_::class,
        Finally_::class,
    ];

    public static function isControlStructure(Node $node): bool
    {
        return in_array($node::class, self::CONTROL_NODES, true);
    }
}
