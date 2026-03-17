<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use function array_key_last;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;

/**
 * Counts return statements per method/function.
 *
 * @internal
 */
final class ReturnStatementsVisitor extends NodeVisitorAbstract
{
    use MethodVisitorTrait;

    /**
     * @psalm-var array<string, int>
     */
    private array $returnsByMethod = [];

    #[\Override]
    public function enterNode(Node $node): void
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

        if ($node instanceof Return_ && $this->isInTrackedMethod()) {
            $this->increment();
        }
    }

    #[\Override]
    public function leaveNode(Node $node): void
    {
        if ($node instanceof Class_ || $node instanceof Trait_ || $node instanceof Enum_) {
            $this->popClass();

            return;
        }

        if ($node instanceof Closure) {
            $this->leaveClosure();
        }
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
