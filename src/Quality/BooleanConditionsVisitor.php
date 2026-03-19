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
 * Collects boolean expression operand counts per method.
 *
 * @internal
 */
final class BooleanConditionsVisitor extends NullableReturnVisitor
{
    use MethodVisitorTrait;

    private ConditionRecorder $recorder;

    public function __construct()
    {
        $this->recorder = new ConditionRecorder();
    }

    protected function doEnterNode(Node $node): void
    {
        if ($node instanceof Class_ || $node instanceof Trait_ || $node instanceof Enum_) {
            $this->pushClass($node);

            return;
        }

        if ($node instanceof Closure) {
            $this->enterClosure();

            return;
        }

        if ($node instanceof ClassMethod || $node instanceof Function_) {
            $this->startMethod($node);

            return;
        }

        if ($this->isInTrackedMethod()) {
            $this->recorder->record($node);
        }
    }

    protected function doLeaveNode(Node $node): void
    {
        if ($node instanceof Class_ || $node instanceof Trait_ || $node instanceof Enum_) {
            $this->popClass();

            return;
        }

        if ($node instanceof Closure) {
            $this->leaveClosure();

            return;
        }

        if ($node instanceof ClassMethod || $node instanceof Function_) {
            $this->recorder->setMethod(null);
        }
    }

    /**
     * @psalm-return array<string, list<array{line: int, count: int}>>
     */
    public function result(): array
    {
        return $this->recorder->result();
    }

    private function startMethod(ClassMethod|Function_ $node): void
    {
        if (! $this->shouldTrackMethod($node)) {
            return;
        }

        $name = $this->resolveMethodName($node);
        if ($name !== null) {
            $this->recorder->initMethod($name);
            $this->recorder->setMethod($name);
        }
    }
}
