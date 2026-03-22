<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Structure;

use function array_pop;
use function count;
use function explode;
use function implode;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;

/**
 * @internal
 */
final class NodeStructureMemberHandlers
{
    public static function handleClass(MetricsState $state, Node $node): void
    {
        if (! $node instanceof Class_ || $node->getAttribute('parent') instanceof New_) {
            return;
        }

        $name = $node->namespacedName?->toString();

        if ($name === null) {
            return;
        }

        $state->classStack[] = $name;
        self::trackNamespace($state, $name);
        $state->push('classLines', $node->getEndLine() - $node->getStartLine() + 1);
        $state->push('methodsPerClass', self::countNonInterfaceMethods($node));
        self::countClassModifier($state, $node);
    }

    public static function handleClassMethod(MetricsState $state, Node $node): void
    {
        if (! $node instanceof ClassMethod || $node->getAttribute('parent') instanceof Interface_) {
            return;
        }

        $state->push('methodLines', $node->getEndLine() - $node->getStartLine() + 1);
        $node->isStatic() ? $state->inc('staticMethods') : $state->inc('nonStaticMethods');

        if ($node->isPublic()) {
            $state->inc('publicMethods');

            return;
        }

        $node->isProtected() ? $state->inc('protectedMethods') : $state->inc('privateMethods');
    }

    public static function handleFunction(MetricsState $state, Node $node): void
    {
        if (! $node instanceof Function_) {
            return;
        }

        $state->push('functionLines', $node->getEndLine() - $node->getStartLine() + 1);
        $state->inc('namedFunctions');
    }

    /**
     * @param Closure|ArrowFunction $node
     */
    public static function handleAnonymousFunction(MetricsState $state, Node $node): void
    {
        $state->push('functionLines', $node->getEndLine() - $node->getStartLine() + 1);
        $state->inc('anonymousFunctions');
    }

    public static function handleClassConst(MetricsState $state, Node $node): void
    {
        if (! $node instanceof ClassConst) {
            return;
        }

        $count = count($node->consts);
        $node->isPublic() ? $state->add('publicClassConstants', $count) : $state->add('nonPublicClassConstants', $count);
    }

    private static function trackNamespace(MetricsState $state, string $name): void
    {
        $parts = explode('\\', $name);

        if (count($parts) <= 1) {
            return;
        }

        array_pop($parts);
        $state->namespaces[implode('\\', $parts)] = true;
    }

    private static function countNonInterfaceMethods(Class_ $node): int
    {
        $count = 0;

        foreach ($node->getMethods() as $method) {
            if (! ($method->getAttribute('parent') instanceof Interface_)) {
                $count++;
            }
        }

        return $count;
    }

    private static function countClassModifier(MetricsState $state, Class_ $node): void
    {
        if ($node->isAbstract()) {
            $state->inc('abstractClasses');

            return;
        }

        $node->isFinal() ? $state->inc('finalClasses') : $state->inc('nonFinalClasses');
    }
}
