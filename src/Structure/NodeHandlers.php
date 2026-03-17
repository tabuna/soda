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
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;

/**
 * @internal
 */
final class NodeHandlers
{
    private const array SUPER_GLOBALS = [
        '_ENV'     => true,
        '_POST'    => true,
        '_GET'     => true,
        '_COOKIE'  => true,
        '_SERVER'  => true,
        '_FILES'   => true,
        '_REQUEST' => true,
    ];

    public static function handleClass(MetricsState $state, Class_ $node): void
    {
        if ($node->getAttribute('parent') instanceof New_) {
            return;
        }

        $name = $node->namespacedName?->toString();

        if ($name === null) {
            return;
        }

        $state->classStack[] = $name;

        $parts = explode('\\', $name);
        if (count($parts) > 1) {
            array_pop($parts);
            $state->namespaces[implode('\\', $parts)] = true;
        }

        $loc = $node->getEndLine() - $node->getStartLine() + 1;

        $state->push('classLines', $loc);

        $methodCount = self::countNonInterfaceMethods($node);
        $state->push('methodsPerClass', $methodCount);

        self::countClassModifier($state, $node);
    }

    public static function handleClassMethod(MetricsState $state, ClassMethod $node): void
    {
        if ($node->getAttribute('parent') instanceof Interface_) {
            return;
        }

        $loc = $node->getEndLine() - $node->getStartLine() + 1;
        $state->push('methodLines', $loc);

        $node->isStatic() ? $state->inc('staticMethods') : $state->inc('nonStaticMethods');

        if ($node->isPublic()) {
            $state->inc('publicMethods');
        } elseif ($node->isProtected()) {
            $state->inc('protectedMethods');
        } else {
            $state->inc('privateMethods');
        }
    }

    public static function handleFunction(MetricsState $state, Function_ $node): void
    {
        $loc = $node->getEndLine() - $node->getStartLine() + 1;
        $state->push('functionLines', $loc);
        $state->inc('namedFunctions');
    }

    /**
     * @param Closure|ArrowFunction $node
     */
    public static function handleAnonymousFunction(MetricsState $state, Node $node): void
    {
        $loc = $node->getEndLine() - $node->getStartLine() + 1;
        $state->push('functionLines', $loc);
        $state->inc('anonymousFunctions');
    }

    public static function handleClassConst(MetricsState $state, ClassConst $node): void
    {
        $count = count($node->consts);
        $node->isPublic() ? $state->add('publicClassConstants', $count) : $state->add('nonPublicClassConstants', $count);
    }

    public static function handleVariable(MetricsState $state, Variable $node): void
    {
        $name = $node->name;

        if ($name instanceof Node\Identifier) {
            $name = $name->toString();
        } elseif (! is_string($name)) {
            return;
        }

        if ($name === 'GLOBALS') {
            $state->inc('globalVariableAccesses');

            return;
        }

        if (isset(self::SUPER_GLOBALS[$name])) {
            $state->inc('superGlobalVariableAccesses');
        }
    }

    private static function countNonInterfaceMethods(Class_ $node): int
    {
        $count = 0;
        foreach ($node->getMethods() as $m) {
            if (! ($m->getAttribute('parent') instanceof Interface_)) {
                $count++;
            }
        }

        return $count;
    }

    private static function countClassModifier(MetricsState $state, Class_ $node): void
    {
        if ($node->isAbstract()) {
            $state->inc('abstractClasses');
        } elseif ($node->isFinal()) {
            $state->inc('finalClasses');
        } else {
            $state->inc('nonFinalClasses');
        }
    }
}
