<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use function assert;

use Bunnivo\Soda\Visitor\NullableReturnVisitor;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Trait_;

/**
 * Computes max control structure nesting depth per method/function.
 *
 * @internal
 */
final class ControlNestingVisitor extends NullableReturnVisitor
{
    use MethodVisitorTrait;

    private NestingTracker $tracker;

    public function __construct()
    {
        $this->tracker = new NestingTracker();
    }

    protected function doEnterNode(Node $node): void
    {
        $syntaxKind = $node->getType();

        if (in_array($syntaxKind, ['Stmt_Class', 'Stmt_Trait', 'Stmt_Enum'], true)) {
            $this->enterClassLikeStatement($node);

            return;
        }

        if ($syntaxKind === 'Expr_Closure') {
            $this->tracker->enterClosure();
            $this->enterClosure();

            return;
        }

        if ($syntaxKind === 'Stmt_ClassMethod' || $syntaxKind === 'Stmt_Function') {
            $this->enterCallableStatement($node);

            return;
        }

        if (ControlStructureMatcher::isControlStructure($node)) {
            $this->tracker->pushControl($node->getStartLine());
        }
    }

    protected function doLeaveNode(Node $node): void
    {
        $syntaxKind = $node->getType();

        if (in_array($syntaxKind, ['Stmt_Class', 'Stmt_Trait', 'Stmt_Enum'], true)) {
            $this->popClass();

            return;
        }

        if ($syntaxKind === 'Expr_Closure') {
            $this->tracker->leaveClosure();
            $this->leaveClosure();

            return;
        }

        if ($syntaxKind === 'Stmt_ClassMethod' || $syntaxKind === 'Stmt_Function') {
            $this->leaveCallableStatement($node);

            return;
        }

        if (ControlStructureMatcher::isControlStructure($node)) {
            $this->tracker->popControl();
        }
    }

    /**
     * @psalm-return array<string, array{depth: int, line: int}>
     */
    public function result(): array
    {
        return $this->tracker->result();
    }

    private function enterClassLikeStatement(Node $node): void
    {
        assert(
            $node instanceof Class_
            || $node instanceof Trait_
            || $node instanceof Enum_,
        );

        $this->pushClass($node);
    }

    private function enterCallableStatement(Node $node): void
    {
        assert(
            $node instanceof ClassMethod
            || $node instanceof Function_,
        );

        $this->startMethod($node);
    }

    private function leaveCallableStatement(Node $node): void
    {
        assert(
            $node instanceof ClassMethod
            || $node instanceof Function_,
        );

        $this->endMethod($node);
    }

    private function startMethod(ClassMethod|Function_ $node): void
    {
        if (! $this->shouldTrackMethod($node)) {
            return;
        }

        $name = $this->resolveMethodName($node);
        if ($name !== null) {
            $this->tracker->startMethod($name, $node->getStartLine());
        }
    }

    private function endMethod(ClassMethod|Function_ $node): void
    {
        if (! $this->shouldTrackMethod($node)) {
            return;
        }

        if ($this->resolveMethodName($node) === null) {
            return;
        }

        $this->tracker->endMethod();
    }
}
