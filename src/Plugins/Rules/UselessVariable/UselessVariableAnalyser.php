<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Plugins\Rules\UselessVariable;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure as ExprClosure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\NodeFinder;

/**
 * Detects useless variables — direct copies of another variable ($a = $b)
 * that are never mutated, passed by reference, captured by a closure,
 * or used after the source is unset.
 *
 * Analysis scope: function / method bodies only (not top-level code).
 */
final readonly class UselessVariableAnalyser
{
    private NodeFinder $finder;

    public function __construct()
    {
        $this->finder = new NodeFinder;
    }

    /**
     * @param Node[] $nodes Top-level AST nodes from a parsed file.
     *
     * @return list<array{line: int, variable: string, source: string}>
     */
    public function analyse(array $nodes): array
    {
        $violations = [];

        foreach ($this->collectFunctionBodies($nodes) as $body) {
            array_push($violations, ...$this->analyseScope($body));
        }

        return $violations;
    }

    // -------------------------------------------------------------------------
    // Scope collection
    // -------------------------------------------------------------------------

    /** @return list<list<Node>> */
    private function collectFunctionBodies(array $nodes): array
    {
        /** @var list<Function_|ClassMethod> $scopes */
        $scopes = $this->finder->find(
            $nodes,
            fn (Node $n): bool => $n instanceof Function_ || $n instanceof ClassMethod,
        );

        return array_values(array_filter(
            array_map(fn (Function_|ClassMethod $s): ?array => $s->stmts, $scopes),
            fn (?array $stmts): bool => $stmts !== null,
        ));
    }

    // -------------------------------------------------------------------------
    // Per-scope analysis
    // -------------------------------------------------------------------------

    /**
     * @param Node[] $stmts
     *
     * @return list<array{line: int, variable: string, source: string}>
     */
    private function analyseScope(array $stmts): array
    {
        $violations = [];

        foreach ($stmts as $i => $stmt) {
            $a = $this->extractDirectAssignment($stmt);

            if ($a !== null && $this->isUseless($a['var'], $a['source'], array_slice($stmts, $i + 1))) {
                $violations[] = ['line' => $a['line'], 'variable' => '$'.$a['var'], 'source' => '$'.$a['source']];
            }
        }

        return $violations;
    }

    /** @return array{var: string, source: string, line: int}|null */
    private function extractDirectAssignment(Node $node): ?array
    {
        if (! ($node instanceof Expression
            && $node->expr instanceof Assign
            && $node->expr->var instanceof Variable
            && $node->expr->expr instanceof Variable
            && is_string($node->expr->var->name)
            && is_string($node->expr->expr->name)
        )) {
            return null;
        }

        return [
            'var'    => $node->expr->var->name,
            'source' => $node->expr->expr->name,
            'line'   => $node->getStartLine(),
        ];
    }

    // -------------------------------------------------------------------------
    // Uselessness checks
    // -------------------------------------------------------------------------

    /** @param Node[] $after Statements that follow the assignment. */
    private function isUseless(string $var, string $source, array $after): bool
    {
        return $this->isUsed($var, $after)
            && ! $this->isMutated($var, $after)
            && ! $this->isEscaped($var, $source, $after);
    }

    /** @param Node[] $after */
    private function isEscaped(string $var, string $source, array $after): bool
    {
        return $this->isPassedByRef($var, $after)
            || $this->isInClosure($var, $after)
            || $this->isObjectMutated($var, $after)
            || $this->isSourceUnset($source, $after);
    }

    /** @param Node[] $nodes */
    private function isUsed(string $var, array $nodes): bool
    {
        return $this->walkScope(
            $nodes,
            static fn (Node $n): bool => $n instanceof Variable && $n->name === $var,
        ) !== [];
    }

    /** @param Node[] $nodes */
    private function isMutated(string $var, array $nodes): bool
    {
        return $this->walkScope($nodes, fn (Node $n): bool => $this->isMutationNode($n, $var)) !== [];
    }

    private function isMutationNode(Node $node, string $var): bool
    {
        return $this->isIncDecOnVar($node, $var)
            || $this->isAssignOpOnVar($node, $var)
            || $this->isDirectAssignOnVar($node, $var);
    }

    private function isIncDecOnVar(Node $node, string $var): bool
    {
        return in_array(get_class($node), [
            'PhpParser\Node\Expr\PreInc',
            'PhpParser\Node\Expr\PostInc',
            'PhpParser\Node\Expr\PreDec',
            'PhpParser\Node\Expr\PostDec',
        ], true) && $node->var instanceof Variable && $node->var->name === $var;
    }

    private function isAssignOpOnVar(Node $node, string $var): bool
    {
        return is_a($node, 'PhpParser\Node\Expr\AssignOp')
            && $node->var instanceof Variable
            && $node->var->name === $var;
    }

    private function isDirectAssignOnVar(Node $node, string $var): bool
    {
        return $node instanceof Assign
            && $node->var instanceof Variable
            && $node->var->name === $var;
    }

    /** @param Node[] $nodes */
    private function isPassedByRef(string $var, array $nodes): bool
    {
        return $this->walkScope($nodes, fn (Node $n): bool => $this->isArgByRef($n, $var)) !== [];
    }

    private function isArgByRef(Node $n, string $var): bool
    {
        return is_a($n, 'PhpParser\Node\Arg')
            && $n->byRef
            && $n->value instanceof Variable
            && $n->value->name === $var;
    }

    /** @param Node[] $nodes */
    private function isInClosure(string $var, array $nodes): bool
    {
        $arrowFns = $this->walkScope($nodes, static fn (Node $n): bool => $n instanceof ArrowFunction);

        return array_filter($arrowFns, function (Node $fn) use ($var): bool {
            assert($fn instanceof ArrowFunction);

            return $this->finder->find([$fn->expr], static fn (Node $n): bool => $n instanceof Variable && $n->name === $var) !== [];
        }) !== []
            || $this->isVarCapturedInClosure($var, $nodes);
    }

    private function isVarCapturedInClosure(string $var, array $nodes): bool
    {
        return array_filter(
            $this->walkScope($nodes, static fn (Node $n): bool => $n instanceof ExprClosure),
            fn (Node $closure): bool => $this->hasCapturedVar($closure, $var),
        ) !== [];
    }

    private function hasCapturedVar(ExprClosure $closure, string $var): bool
    {
        return array_filter(
            $closure->uses,
            static fn ($use): bool => $use->var instanceof Variable && $use->var->name === $var,
        ) !== [];
    }

    /** @param Node[] $nodes */
    private function isObjectMutated(string $var, array $nodes): bool
    {
        return $this->walkScope($nodes, fn (Node $n): bool => $this->isPropAssignOnVar($n, $var)) !== [];
    }

    private function isPropAssignOnVar(Node $n, string $var): bool
    {
        return $n instanceof Assign
            && is_a($n->var, 'PhpParser\Node\Expr\PropertyFetch')
            && $n->var->var instanceof Variable
            && $n->var->var->name === $var;
    }

    /** @param Node[] $nodes */
    private function isSourceUnset(string $source, array $nodes): bool
    {
        return array_filter(
            $this->walkScope($nodes, static fn (Node $n): bool => is_a($n, 'PhpParser\Node\Stmt\Unset_')),
            fn (Node $unset): bool => $this->isVarInUnset($unset, $source),
        ) !== [];
    }

    private function isVarInUnset(Node $unset, string $var): bool
    {
        /** @var Unset_ $unset */
        return array_filter(
            $unset->vars,
            static fn ($v): bool => $v instanceof Variable && $v->name === $var,
        ) !== [];
    }

    // -------------------------------------------------------------------------
    // Scope-aware node walker
    // -------------------------------------------------------------------------

    /**
     * Find nodes matching $predicate without crossing scope boundaries
     * (closures, arrow functions, nested functions / methods).
     *
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    private function walkScope(array $nodes, Closure $predicate): array
    {
        $found = [];

        foreach ($nodes as $node) {
            if (! $node instanceof Node) {
                continue;
            }

            if ($predicate($node)) {
                $found[] = $node;
            }

            if (! $this->isScopeBoundary($node)) {
                array_push($found, ...$this->walkSubNodes($node, $predicate));
            }
        }

        return $found;
    }

    /** @return Node[] */
    private function walkSubNodes(Node $node, Closure $predicate): array
    {
        $found = [];

        foreach ($node->getSubNodeNames() as $subName) {
            /** @phpstan-ignore property.dynamicName */
            $sub = $node->$subName;

            if (is_array($sub)) {
                array_push($found, ...$this->walkScope($sub, $predicate));
            } elseif ($sub instanceof Node) {
                array_push($found, ...$this->walkScope([$sub], $predicate));
            }
        }

        return $found;
    }

    private function isScopeBoundary(Node $node): bool
    {
        return $node instanceof Function_
            || $node instanceof ClassMethod
            || $node instanceof ExprClosure
            || $node instanceof ArrowFunction;
    }
}
