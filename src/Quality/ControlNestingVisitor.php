<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Visitor\NullableReturnVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
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
        if ($node instanceof Class_ || $node instanceof Trait_ || $node instanceof Enum_) {
            $this->pushClass($node);

            return;
        }

        if ($node instanceof Closure) {
            $this->tracker->enterClosure();
            $this->enterClosure();

            return;
        }

        if ($node instanceof ClassMethod || $node instanceof Function_) {
            $this->startMethod($node);

            return;
        }

        if (ControlStructureMatcher::isControlStructure($node)) {
            $this->tracker->pushControl($node->getStartLine());
        }
    }

    protected function doLeaveNode(Node $node): void
    {
        if ($node instanceof Class_ || $node instanceof Trait_ || $node instanceof Enum_) {
            $this->popClass();

            return;
        }

        if ($node instanceof Closure) {
            $this->tracker->leaveClosure();
            $this->leaveClosure();

            return;
        }

        if ($node instanceof ClassMethod || $node instanceof Function_) {
            $this->endMethod($node);

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
