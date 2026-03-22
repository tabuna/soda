<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Support;

use function array_key_last;
use function array_pop;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;

/**
 * @internal
 */
trait MethodVisitorTrait
{
    /**
     * @psalm-var list<non-empty-string>
     */
    private array $classStack = [];

    private bool $inClosure = false;

    protected function pushClass(Class_|Trait_|Enum_ $node): void
    {
        if ($node->getAttribute('parent') instanceof New_) {
            return;
        }

        $name = $node->namespacedName?->toString();
        if ($name === null || $name === '') {
            return;
        }

        $this->classStack[] = $name;
    }

    protected function popClass(): void
    {
        if ($this->classStack !== []) {
            array_pop($this->classStack);
        }
    }

    protected function enterClosure(): void
    {
        $this->inClosure = true;
    }

    protected function leaveClosure(): void
    {
        $this->inClosure = false;
    }

    protected function shouldTrackMethod(ClassMethod|Function_ $node): bool
    {
        if ($node instanceof ClassMethod && $node->getAttribute('parent') instanceof Interface_) {
            return false;
        }

        return ! ($node instanceof ClassMethod && $node->isAbstract());
    }

    protected function resolveMethodName(ClassMethod|Function_ $node): ?string
    {
        if ($node instanceof ClassMethod) {
            $class = $this->classStack !== [] ? $this->classStack[array_key_last($this->classStack)] : null;

            return $class !== null ? $class.'::'.$node->name->toString() : null;
        }

        return $node->namespacedName?->toString();
    }

    protected function isInTrackedMethod(): bool
    {
        return ! $this->inClosure;
    }
}
