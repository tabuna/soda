<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Complexity;

use function assert;
use function is_array;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use SebastianBergmann\Complexity\Complexity;
use SebastianBergmann\Complexity\ComplexityCollection;
use SebastianBergmann\Complexity\CyclomaticComplexityCalculatingVisitor;

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

            $name = $this->classMethodName($node);
        } else {
            $name = $this->functionName($node);
        }

        $statements = $node->getStmts();
        assert(is_array($statements));
        $this->result[] = new Complexity($name, $this->cyclomaticComplexity($statements));

        return $this->shortCircuitTraversal ? NodeVisitor::DONT_TRAVERSE_CHILDREN : null;
    }

    public function result(): ComplexityCollection
    {
        return ComplexityCollection::fromList(...$this->result);
    }

    /**
     * @param Stmt[] $statements
     *
     * @return positive-int
     */
    private function cyclomaticComplexity(array $statements): int
    {
        $traverser = new NodeTraverser();
        $visitor = new CyclomaticComplexityCalculatingVisitor();
        $traverser->addVisitor($visitor);
        $traverser->traverse($statements);

        return $visitor->cyclomaticComplexity();
    }

    /** @return non-empty-string */
    private function classMethodName(ClassMethod $node): string
    {
        $parent = $node->getAttribute('parent');

        assert($parent instanceof Class_ || $parent instanceof Trait_ || $parent instanceof Enum_);

        if ($parent->getAttribute('parent') instanceof New_) {
            return 'anonymous class';
        }

        assert(isset($parent->namespacedName));
        assert($parent->namespacedName instanceof Name);

        return $parent->namespacedName->toString().'::'.$node->name->toString();
    }

    /** @return non-empty-string */
    private function functionName(Function_ $node): string
    {
        assert(isset($node->namespacedName));
        assert($node->namespacedName instanceof Name);

        $functionName = $node->namespacedName->toString();

        assert($functionName !== '');

        return $functionName;
    }
}
