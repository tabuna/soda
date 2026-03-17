<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Structure;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Const_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;

/**
 * @internal
 */
final class StructureNodeHandlers
{
    public static function handleNamespace(MetricsState $state, Node $node): bool
    {
        if (! $node instanceof Namespace_) {
            return false;
        }

        $name = $node->name?->toString() ?? '';

        if ($name !== '') {
            $state->namespaces[$name] = true;
        }

        return true;
    }

    public static function handleTypeDeclarations(MetricsState $state, Node $node): bool
    {
        if ($node instanceof Interface_) {
            $state->inc('interfaces');

            return true;
        }

        if ($node instanceof Trait_) {
            $state->inc('traits');

            return true;
        }

        if ($node instanceof Class_) {
            NodeHandlers::handleClass($state, $node);

            return true;
        }

        return false;
    }

    public static function handleMembers(MetricsState $state, Node $node): bool
    {
        return match (true) {
            $node instanceof ClassMethod => self::handleClassMethodMember($state, $node),
            $node instanceof Function_   => self::handleFunctionMember($state, $node),
            $node instanceof Closure,
            $node instanceof ArrowFunction => self::handleAnonymousMember($state, $node),
            $node instanceof ClassConst    => self::handleClassConstMember($state, $node),
            default                        => false,
        };
    }

    private static function handleClassMethodMember(MetricsState $state, ClassMethod $node): bool
    {
        NodeHandlers::handleClassMethod($state, $node);

        return true;
    }

    private static function handleFunctionMember(MetricsState $state, Function_ $node): bool
    {
        NodeHandlers::handleFunction($state, $node);

        return true;
    }

    private static function handleAnonymousMember(MetricsState $state, Closure|ArrowFunction $node): bool
    {
        NodeHandlers::handleAnonymousFunction($state, $node);

        return true;
    }

    private static function handleClassConstMember(MetricsState $state, ClassConst $node): bool
    {
        NodeHandlers::handleClassConst($state, $node);

        return true;
    }

    public static function handleGlobals(MetricsState $state, Node $node): bool
    {
        if ($node instanceof Const_) {
            $state->inc('globalConstants');

            return true;
        }

        if ($node instanceof Global_) {
            $state->inc('globalVariableAccesses');

            return true;
        }

        return false;
    }
}
