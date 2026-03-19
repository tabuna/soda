<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Structure;

use function assert;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;

/**
 * @internal
 */
final class StructureNodeHandlers
{
    public static function handleNamespace(MetricsState $state, Node $node): bool
    {
        if ($node->getType() !== 'Stmt_Namespace') {
            return false;
        }

        assert($node instanceof Namespace_);

        $name = $node->name?->toString() ?? '';

        if ($name !== '') {
            $state->namespaces[$name] = true;
        }

        return true;
    }

    public static function handleTypeDeclarations(MetricsState $state, Node $node): bool
    {
        return match ($node->getType()) {
            'Stmt_Interface' => self::incrementDeclaredInterface($state),
            'Stmt_Trait'     => self::incrementDeclaredTrait($state),
            'Stmt_Class'     => self::registerDeclaredClass($state, $node),
            default          => false,
        };
    }

    public static function handleMembers(MetricsState $state, Node $node): bool
    {
        return match ($node->getType()) {
            'Stmt_ClassMethod'   => self::handleClassMethodMember($state, $node),
            'Stmt_Function'      => self::handleFunctionMember($state, $node),
            'Expr_Closure',
            'Expr_ArrowFunction' => self::handleAnonymousMember($state, $node),
            'Stmt_ClassConst'    => self::handleClassConstMember($state, $node),
            default              => false,
        };
    }

    private static function incrementDeclaredInterface(MetricsState $state): bool
    {
        $state->inc('interfaces');

        return true;
    }

    private static function incrementDeclaredTrait(MetricsState $state): bool
    {
        $state->inc('traits');

        return true;
    }

    private static function registerDeclaredClass(MetricsState $state, Node $node): bool
    {
        assert($node instanceof Class_);

        NodeHandlers::handleClass($state, $node);

        return true;
    }

    private static function handleClassMethodMember(MetricsState $state, Node $node): bool
    {
        assert($node instanceof ClassMethod);

        NodeHandlers::handleClassMethod($state, $node);

        return true;
    }

    private static function handleFunctionMember(MetricsState $state, Node $node): bool
    {
        assert($node instanceof Function_);

        NodeHandlers::handleFunction($state, $node);

        return true;
    }

    private static function handleAnonymousMember(MetricsState $state, Node $node): bool
    {
        assert($node instanceof Closure || $node instanceof ArrowFunction);

        NodeHandlers::handleAnonymousFunction($state, $node);

        return true;
    }

    private static function handleClassConstMember(MetricsState $state, Node $node): bool
    {
        assert($node instanceof ClassConst);

        NodeHandlers::handleClassConst($state, $node);

        return true;
    }

    public static function handleGlobals(MetricsState $state, Node $node): bool
    {
        return match ($node->getType()) {
            'Stmt_Const'  => self::incrementGlobalConstantDeclaration($state),
            'Stmt_Global' => self::incrementGlobalVariableAccess($state),
            default       => false,
        };
    }

    private static function incrementGlobalConstantDeclaration(MetricsState $state): bool
    {
        $state->inc('globalConstants');

        return true;
    }

    private static function incrementGlobalVariableAccess(MetricsState $state): bool
    {
        $state->inc('globalVariableAccesses');

        return true;
    }
}
