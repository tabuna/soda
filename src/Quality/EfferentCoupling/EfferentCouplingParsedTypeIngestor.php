<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EfferentCoupling;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;

/**
 * @internal
 */
final readonly class EfferentCouplingParsedTypeIngestor
{
    public function __construct(
        private EfferentCouplingTypeSink $sink,
    ) {}

    public function ingest(Node|ComplexType|null $type): void
    {
        if (! $type instanceof Node) {
            return;
        }

        if ($type instanceof NullableType) {
            $this->ingest($type->type);

            return;
        }

        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            foreach ($type->types as $inner) {
                $this->ingest($inner);
            }

            return;
        }

        if ($type instanceof Identifier) {
            $this->sink->recordNonBuiltinNamedIdentifier($type);

            return;
        }

        if ($type instanceof Name) {
            $this->sink->registerReferencedClassName($type);
        }
    }
}
