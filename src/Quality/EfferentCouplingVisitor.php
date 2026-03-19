<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\EfferentCoupling\EfferentCouplingClassScanner;
use Bunnivo\Soda\Quality\EfferentCoupling\EfferentCouplingGraph;
use Bunnivo\Soda\Quality\EfferentCoupling\EfferentCouplingMemberScanner;
use Bunnivo\Soda\Quality\EfferentCoupling\EfferentCouplingTypeSink;
use Bunnivo\Soda\Visitor\NullableReturnVisitor;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;

/**
 * Efferent coupling (Ce): distinct external types referenced by a class or trait.
 *
 * @internal
 */
final class EfferentCouplingVisitor extends NullableReturnVisitor
{
    private readonly EfferentCouplingGraph $graph;

    private readonly EfferentCouplingTypeSink $types;

    private readonly EfferentCouplingClassScanner $classScanner;

    private readonly EfferentCouplingMemberScanner $memberScanner;

    public function __construct()
    {
        $this->graph = new EfferentCouplingGraph;
        $this->types = new EfferentCouplingTypeSink($this->graph);
        $this->classScanner = new EfferentCouplingClassScanner($this->graph, $this->types);
        $this->memberScanner = EfferentCouplingMemberScanner::wiredToTypeSink($this->types);
    }

    protected function doEnterNode(Node $node): void
    {
        if ($node instanceof Class_ || $node instanceof Trait_) {
            $this->classScanner->enter($node);

            return;
        }

        if ($this->graph->currentOwner() === null) {
            return;
        }

        $this->memberScanner->enter($node);
    }

    protected function doLeaveNode(Node $node): void
    {
        $this->classScanner->leave($node);
    }

    /**
     * @psalm-return array<string, int>
     */
    public function result(): array
    {
        return $this->graph->couplingCountsByClass();
    }
}
