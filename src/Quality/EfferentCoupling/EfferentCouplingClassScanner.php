<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EfferentCoupling;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;

/**
 * Enters/leaves class and trait declarations for Ce tracking.
 *
 * @internal
 */
final readonly class EfferentCouplingClassScanner
{
    public function __construct(
        private EfferentCouplingGraph $graph,
        private EfferentCouplingTypeSink $types,
    ) {}

    public function enter(Class_|Trait_ $node): void
    {
        if ($node->getAttribute('parent') instanceof New_) {
            $this->captureAnonymousShell($node);

            return;
        }

        $name = $node->namespacedName?->toString();

        if ($name === null || $name === '') {
            return;
        }

        $this->graph->pushFrame($name, $this->resolveExtends($node));
        $this->captureDeclaredSupertypes($node);
    }

    private function captureAnonymousShell(Class_|Trait_ $node): void
    {
        if ($node instanceof Class_) {
            $this->captureClassExtendsAndInterfaces($node);
        }

        $this->captureTraitAdoptions($node);
    }

    private function captureDeclaredSupertypes(Class_|Trait_ $node): void
    {
        if ($node instanceof Class_) {
            $this->captureClassExtendsAndInterfaces($node);
        }

        $this->captureTraitAdoptions($node);
    }

    private function captureClassExtendsAndInterfaces(Class_ $node): void
    {
        $this->types->registerReferencedClassName($node->extends);

        foreach ($node->implements as $interfaceName) {
            $this->types->registerReferencedClassName($interfaceName);
        }
    }

    private function captureTraitAdoptions(Class_|Trait_ $node): void
    {
        foreach ($node->getTraitUses() as $use) {
            foreach ($use->traits as $traitName) {
                $this->types->registerReferencedClassName($traitName);
            }
        }
    }

    /**
     * @psalm-return non-empty-string|null
     */
    private function resolveExtends(Class_|Trait_ $node): ?string
    {
        if (! $node instanceof Class_ || ! $node->extends instanceof Name) {
            return null;
        }

        $resolved = $this->types->publicFqcnFromName($node->extends);

        return ($resolved !== null && $resolved !== '') ? $resolved : null;
    }

    public function leave(Node $node): void
    {
        if (! ($node instanceof Class_) && ! ($node instanceof Trait_)) {
            return;
        }

        if ($node->getAttribute('parent') instanceof New_) {
            return;
        }

        $name = $node->namespacedName?->toString();

        if ($name === null || $name === '') {
            return;
        }

        $this->graph->popFrame();
    }
}
