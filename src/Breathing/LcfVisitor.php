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

        if ($node instanceof For_ || $node instanceof Foreach_ || $node instanceof While_ || $node instanceof Do_) {
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

    private function hasBody(Node $node): bool
    {
        return $node instanceof If_
            || $node instanceof Switch_
            || $node instanceof For_
            || $node instanceof Foreach_
            || $node instanceof While_
            || $node instanceof Do_;
    }
}
