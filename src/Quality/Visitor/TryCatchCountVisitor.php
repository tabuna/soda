<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Visitor;

use function array_key_last;

use Bunnivo\Soda\Quality\Support\MethodVisitorTrait;
use Bunnivo\Soda\Visitor\NullableReturnVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TryCatch;

/**
 * Counts try/catch blocks per method or top-level function.
 *
 * @internal
 */
final class TryCatchCountVisitor extends NullableReturnVisitor
{
    use MethodVisitorTrait;

    /**
     * @psalm-var array<string, int>
     */
    private array $tryCatchByMethod = [];

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

        if (! $node instanceof TryCatch || ! $this->isInTrackedMethod()) {
            return;
        }

        $this->increment();
    }

    /**
     * @psalm-return array<string, int>
     */
    public function result(): array
    {
        return $this->tryCatchByMethod;
    }

    private function startMethod(ClassMethod|Function_ $node): void
    {
        if (! $this->shouldTrackMethod($node)) {
            return;
        }

        $name = $this->resolveMethodName($node);
        if ($name !== null) {
            $this->tryCatchByMethod[$name] = 0;
        }
    }

    private function increment(): void
    {
        $last = array_key_last($this->tryCatchByMethod);
        if ($last !== null) {
            $this->tryCatchByMethod[$last]++;
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
        }
    }
}
