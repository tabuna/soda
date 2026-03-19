<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EfferentCoupling;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;

/**
 * new / static / instanceof class operands.
 *
 * @internal
 */
final readonly class EfferentCouplingInvokeShapeScanner
{
    public function __construct(
        private EfferentCouplingTypeSink $types,
    ) {}

    public function onNode(Node $node): void
    {
        if ($node instanceof New_) {
            $this->types->registerClassOperandFromExpression($node->class);

            return;
        }

        if ($node instanceof StaticCall) {
            $this->types->registerClassOperandFromExpression($node->class);

            return;
        }

        if ($node instanceof StaticPropertyFetch) {
            $this->types->registerClassOperandFromExpression($node->class);

            return;
        }

        if ($node instanceof ClassConstFetch) {
            $this->types->registerClassOperandFromExpression($node->class);

            return;
        }

        if ($node instanceof Instanceof_) {
            $this->types->registerClassOperandFromExpression($node->class);
        }
    }
}
