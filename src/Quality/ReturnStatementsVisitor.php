<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use function array_key_last;

use Bunnivo\Soda\Visitor\NullableReturnVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Trait_;

/**
 * Counts return statements per method/function.
 *
 * @internal
 */
final class ReturnStatementsVisitor extends NullableReturnVisitor
{
    use MethodVisitorTrait;

    /**
     * @psalm-var array<string, int>
     */
    private array $returnsByMethod = [];

    protected function doEnterNode(Node $node): void
    {
        if ($this->isClassLike($node)) {
            $this->pushClass($node);

            return;
        }

        if ($node instanceof Closure) {
            $this->enterClosure();

            return;
        }

        if ($this->isMethodLike($node)) {
            $this->startMethod($node);

            return;
        }

        if ($node instanceof Return_ && $this->isInTrackedMethod()) {
            $this->increment();
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

    private function isClassLike(Node $node): bool
    {
        return $node instanceof Class_ || $node instanceof Trait_ || $node instanceof Enum_;
    }

    private function isMethodLike(Node $node): bool
    {
        return $node instanceof ClassMethod || $node instanceof Function_;
    }

    /**
     * @psalm-return array<string, int>
     */
    public function result(): array
    {
        return $this->returnsByMethod;
    }

    private function startMethod(ClassMethod|Function_ $node): void
    {
        if (! $this->shouldTrackMethod($node)) {
            return;
        }

        $name = $this->resolveMethodName($node);
        if ($name !== null) {
            $this->returnsByMethod[$name] = 0;
        }
    }

    private function increment(): void
    {
        $last = array_key_last($this->returnsByMethod);
        if ($last !== null) {
            $this->returnsByMethod[$last]++;
        }
    }
}
