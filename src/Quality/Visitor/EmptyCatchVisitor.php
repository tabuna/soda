<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Visitor;

use Bunnivo\Soda\Quality\Support\MethodVisitorTrait;
use Bunnivo\Soda\Visitor\NullableReturnVisitor;

use function explode;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Trait_;

use function str_contains;

/**
 * Collects empty catch blocks with their enclosing callable context.
 *
 * @internal
 */
final class EmptyCatchVisitor extends NullableReturnVisitor
{
    use MethodVisitorTrait;

    /**
     * @psalm-var list<array{line: int, method: string|null, class: string|null}>
     */
    private array $result = [];

    private ?string $currentMethod = null;

    private ?string $currentClass = null;

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

        if ($node instanceof Catch_ && $node->stmts === []) {
            $this->result[] = [
                'line'   => $node->getStartLine(),
                'method' => $this->currentMethod,
                'class'  => $this->currentClass,
            ];
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
            $this->currentMethod = null;
            $this->currentClass = null;
        }
    }

    /**
     * @psalm-return list<array{line: int, method: string|null, class: string|null}>
     */
    public function result(): array
    {
        return $this->result;
    }

    private function startMethod(ClassMethod|Function_ $node): void
    {
        if (! $this->shouldTrackMethod($node)) {
            return;
        }

        $this->currentMethod = $this->resolveMethodName($node);
        $this->currentClass = $this->currentMethod !== null && str_contains($this->currentMethod, '::')
            ? explode('::', $this->currentMethod, 2)[0]
            : null;
    }
}
