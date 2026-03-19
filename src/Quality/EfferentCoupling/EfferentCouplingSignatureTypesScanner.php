<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EfferentCoupling;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

/**
 * Property and callable signatures (params + return types).
 *
 * @internal
 */
final readonly class EfferentCouplingSignatureTypesScanner
{
    public function __construct(
        private EfferentCouplingTypeSink $types,
    ) {}

    public function onNode(Node $node): void
    {
        if ($node instanceof Property) {
            $this->types->ingestParsedTypeHint($node->type);

            return;
        }

        if (! $node instanceof ClassMethod && ! $node instanceof Closure && ! $node instanceof ArrowFunction) {
            return;
        }

        foreach ($node->params as $parameter) {
            $this->types->ingestParsedTypeHint($parameter->type);
        }

        $this->types->ingestParsedTypeHint($node->getReturnType());
    }
}
