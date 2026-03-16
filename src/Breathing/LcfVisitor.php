<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

use PhpParser\Node;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class LcfVisitor extends NodeVisitorAbstract
{
    private int $nCond = 0;

    private int $nLoop = 0;

    private int $depth = 0;

    private int $depthMax = 0;

    #[\Override]
    public function enterNode(Node $node): void
    {
        if ($node instanceof If_ || $node instanceof Switch_) {
            $this->nCond++;
        }

        if ($this->isLoop($node)) {
            $this->nLoop++;
        }

        if ($this->hasBody($node)) {
            $this->depth++;
            $this->depthMax = max($this->depthMax, $this->depth);
        }
    }

    #[\Override]
    public function leaveNode(Node $node): void
    {
        if ($this->hasBody($node)) {
            $this->depth--;
        }
    }

    public function lcf(): float
    {
        return 1.0 + 0.3 * (float) $this->nCond + 0.2 * (float) $this->nLoop + 0.4 * (float) $this->depthMax;
    }

    private const array BODY_NODES = [
        If_::class,
        Switch_::class,
        For_::class,
        Foreach_::class,
        While_::class,
        Do_::class,
    ];

    private const array LOOP_NODES = [
        For_::class,
        Foreach_::class,
        While_::class,
        Do_::class,
    ];

    private function isLoop(Node $node): bool
    {
        return in_array($node::class, self::LOOP_NODES, true);
    }

    private function hasBody(Node $node): bool
    {
        return in_array($node::class, self::BODY_NODES, true);
    }
}
