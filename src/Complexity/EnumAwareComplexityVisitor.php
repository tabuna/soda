<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Complexity;

use function assert;
use function is_array;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use SebastianBergmann\Complexity\Complexity;
use SebastianBergmann\Complexity\ComplexityCollection;

/**
 * Fork of Sebastian's ComplexityCalculatingVisitor with PHP 8.1 Enum support.
 * Upstream asserts Class_|Trait_ only and crashes on Enum_ methods.
 *
 * REMOVE_WHEN sebastian/complexity adds Enum support — grep for REMOVE_WHEN.
 */
final class EnumAwareComplexityVisitor extends NodeVisitorAbstract
{
    /** @var list<Complexity> */
    private array $result = [];

    public function __construct(
        private readonly bool $shortCircuitTraversal,
    ) {}

    public function enterNode(Node $node): ?int
    {
        if (! $node instanceof ClassMethod && ! $node instanceof Function_) {
            return null;
        }

        if ($node instanceof ClassMethod) {
            if ($node->getAttribute('parent') instanceof Interface_ || $node->isAbstract()) {
                return null;
            }

            $name = EnumAwareComplexityQualifiedNameBuilder::forClassMethod($node);
        } else {
            $name = EnumAwareComplexityQualifiedNameBuilder::forFunction($node);
        }

        $statements = $node->getStmts();
        assert(is_array($statements));
        $this->result[] = new Complexity($name, EnumAwareComplexityCyclomaticRunner::fromStatements($statements));

        return $this->shortCircuitTraversal ? NodeVisitor::DONT_TRAVERSE_CHILDREN : null;
    }

    public function result(): ComplexityCollection
    {
        return ComplexityCollection::fromList(...$this->result);
    }
}
