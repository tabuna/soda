<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EfferentCoupling;

use function ltrim;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

use function strtolower;

/**
 * Records type references (params, returns, properties) into {@see EfferentCouplingGraph}.
 *
 * @internal
 */
final readonly class EfferentCouplingTypeSink
{
    public function __construct(
        private EfferentCouplingGraph $graph,
    ) {}

    public function ingestParsedTypeHint(Node|ComplexType|null $type): void
    {
        (new EfferentCouplingParsedTypeIngestor($this))->ingest($type);
    }

    public function recordNonBuiltinNamedIdentifier(Identifier $type): void
    {
        if (! $this->isPhpBuiltinIdentifier($type->toLowerString())) {
            $this->graph->addEdge($type->toString());
        }
    }

    public function registerReferencedClassName(?Name $name): void
    {
        if (! $name instanceof Name) {
            return;
        }

        $lower = strtolower($name->toString());

        if ($lower === 'self' || $lower === 'static') {
            return;
        }

        if ($lower === 'parent') {
            $parentClass = $this->graph->currentExtends();

            if ($parentClass !== null) {
                $this->graph->addEdge($parentClass);
            }

            return;
        }

        $fqcn = $this->fullyQualifiedClassName($name);

        if ($fqcn !== null) {
            $this->graph->addEdge($fqcn);
        }
    }

    /**
     * @psalm-return non-empty-string|null
     */
    public function publicFqcnFromName(Name $name): ?string
    {
        return $this->fullyQualifiedClassName($name);
    }

    public function registerClassOperandFromExpression(Node $expr): void
    {
        if ($expr instanceof Name) {
            $this->registerReferencedClassName($expr);
        }
    }

    /**
     * @psalm-return non-empty-string|null
     */
    private function fullyQualifiedClassName(Name $name): ?string
    {
        $s = ltrim($name->toString(), '\\');

        return $s !== '' ? $s : null;
    }

    private function isPhpBuiltinIdentifier(string $lower): bool
    {
        return isset([
            'int'      => true,
            'string'   => true,
            'float'    => true,
            'bool'     => true,
            'array'    => true,
            'callable' => true,
            'iterable' => true,
            'object'   => true,
            'mixed'    => true,
            'never'    => true,
            'void'     => true,
            'false'    => true,
            'true'     => true,
            'null'     => true,
            'static'   => true,
        ][$lower]);
    }
}
