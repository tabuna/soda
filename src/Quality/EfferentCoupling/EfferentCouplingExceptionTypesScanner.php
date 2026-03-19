<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EfferentCoupling;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;

/**
 * catch () type list.
 *
 * @internal
 */
final readonly class EfferentCouplingExceptionTypesScanner
{
    public function __construct(
        private EfferentCouplingTypeSink $types,
    ) {}

    public function onNode(Node $node): void
    {
        if (! $node instanceof Catch_) {
            return;
        }

        foreach ($node->types as $catchType) {
            $this->types->registerReferencedClassName($catchType);
        }
    }
}
