<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Base for AST visitors that never modify the tree (always return null).
 *
 * @internal
 */
abstract class NullableReturnVisitor extends NodeVisitorAbstract
{
    #[\Override]
    public function enterNode(Node $node): array|int|Node|null
    {
        $this->doEnterNode($node);

        return null;
    }

    #[\Override]
    public function leaveNode(Node $node): array|int|Node|null
    {
        $this->doLeaveNode($node);

        return null;
    }

    protected function doEnterNode(Node $node): void {}

    protected function doLeaveNode(Node $node): void {}
}
