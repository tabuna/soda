<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Structure;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;

/**
 * @internal
 */
final class StructureNodeHandlers
{
    public static function handleNamespace(MetricsState $state, Node $node): void
    {
        if (! $node instanceof Namespace_) {
            return;
        }

        $name = $node->name?->toString() ?? '';

        if ($name !== '') {
            $state->namespaces[$name] = true;
        }
    }

    public static function handleTypeDeclarations(MetricsState $state, Node $node): void
    {
        if ($node->getType() === 'Stmt_Interface') {
            $state->inc('interfaces');

            return;
        }

        if ($node->getType() === 'Stmt_Trait') {
            $state->inc('traits');

            return;
        }

        if ($node->getType() === 'Stmt_Class') {
            NodeStructureMemberHandlers::handleClass($state, $node);
        }
    }

    public static function handleMembers(MetricsState $state, Node $node): void
    {
        if ($node->getType() === 'Stmt_ClassMethod') {
            NodeStructureMemberHandlers::handleClassMethod($state, $node);

            return;
        }

        if ($node->getType() === 'Stmt_Function') {
            NodeStructureMemberHandlers::handleFunction($state, $node);

            return;
        }

        if ($node->getType() === 'Expr_Closure' || $node->getType() === 'Expr_ArrowFunction') {
            NodeStructureMemberHandlers::handleAnonymousFunction($state, $node);

            return;
        }

        if ($node->getType() === 'Stmt_ClassConst') {
            NodeStructureMemberHandlers::handleClassConst($state, $node);
        }
    }

    public static function handleGlobals(MetricsState $state, Node $node): void
    {
        if ($node->getType() === 'Stmt_Const') {
            $state->inc('globalConstants');

            return;
        }

        if ($node->getType() === 'Stmt_Global') {
            $state->inc('globalVariableAccesses');
        }
    }
}
