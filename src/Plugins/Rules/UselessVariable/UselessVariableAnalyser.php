<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Plugins\Rules\UselessVariable;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;

/**
 * Detects useless variables — direct copies of another variable ($a = $b)
 * that are never mutated, passed by reference, captured by a closure,
 * or used after the source is unset.
 *
 * Analysis scope: function / method bodies only (not top-level code).
 */
final class UselessVariableAnalyser
{
    private NodeFinder $finder;

    public function __construct()
    {
        $this->finder = new NodeFinder;
    }

    /**
     * @param  Node[]  $nodes  Top-level AST nodes from a parsed file.
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

    /** @return list<list<Node\Stmt>> */
    private function collectFunctionBodies(array $nodes): array
    {
        $bodies = [];

        $scopes = $this->finder->find(
            $nodes,
            static fn (Node $n): bool => $n instanceof Stmt\Function_ || $n instanceof Stmt\ClassMethod,
        );

        foreach ($scopes as $scope) {
            /** @var Stmt\Function_|Stmt\ClassMethod $scope */
            if ($scope->stmts !== null) {
                $bodies[] = $scope->stmts;
            }
        }

        return $bodies;
    }

    // -------------------------------------------------------------------------
    // Per-scope analysis
    // -------------------------------------------------------------------------

    /**
     * @param  Node\Stmt[]  $stmts
     * @return list<array{line: int, variable: string, source: string}>
     */
    private function analyseScope(array $stmts): array
    {
        $violations = [];

        foreach ($stmts as $i => $stmt) {
            $assignment = $this->extractDirectAssignment($stmt);

            if ($assignment === null) {
                continue;
            }

            ['var' => $var, 'source' => $source, 'line' => $line] = $assignment;

            if ($this->isUseless($var, $source, array_slice($stmts, $i + 1))) {
                $violations[] = ['line' => $line, 'variable' => '$' . $var, 'source' => '$' . $source];
            }
        }

        return $violations;
    }

    /** @return array{var: string, source: string, line: int}|null */
    private function extractDirectAssignment(Node $node): ?array
    {
        if (! $node instanceof Stmt\Expression
            || ! $node->expr instanceof Expr\Assign
            || ! $node->expr->var instanceof Expr\Variable
            || ! $node->expr->expr instanceof Expr\Variable
        ) {
            return null;
        }

        $varName    = $node->expr->var->name;
        $sourceName = $node->expr->expr->name;

        if (! is_string($varName) || ! is_string($sourceName)) {
            return null;
        }

        return ['var' => $varName, 'source' => $sourceName, 'line' => $node->getStartLine()];
    }

    // -------------------------------------------------------------------------
    // Uselessness checks
    // -------------------------------------------------------------------------

    /** @param Node[] $after Statements that follow the assignment. */
    private function isUseless(string $var, string $source, array $after): bool
    {
        return $this->isUsed($var, $after)
            && ! $this->isMutated($var, $after)
            && ! $this->isPassedByRef($var, $after)
            && ! $this->isInClosure($var, $after)
            && ! $this->isObjectMutated($var, $after)
            && ! $this->isSourceUnset($source, $after);
    }

    /** @param Node[] $nodes */
    private function isUsed(string $var, array $nodes): bool
    {
        return $this->walkScope(
            $nodes,
            static fn (Node $n): bool => $n instanceof Expr\Variable && $n->name === $var,
        ) !== [];
    }

    /** @param Node[] $nodes */
    private function isMutated(string $var, array $nodes): bool
    {
        return $this->walkScope($nodes, fn (Node $n): bool => $this->isMutationNode($n, $var)) !== [];
    }

    private function isMutationNode(Node $node, string $var): bool
    {
        if (($node instanceof Expr\PreInc || $node instanceof Expr\PostInc
            || $node instanceof Expr\PreDec || $node instanceof Expr\PostDec)
            && $node->var instanceof Expr\Variable && $node->var->name === $var
        ) {
            return true;
        }

        if ($node instanceof Expr\AssignOp
            && $node->var instanceof Expr\Variable && $node->var->name === $var
        ) {
            return true;
        }

        if ($node instanceof Expr\Assign
            && $node->var instanceof Expr\Variable && $node->var->name === $var
        ) {
            return true;
        }

        return false;
    }

    /** @param Node[] $nodes */
    private function isPassedByRef(string $var, array $nodes): bool
    {
        return $this->walkScope(
            $nodes,
            static fn (Node $n): bool => $n instanceof Node\Arg
                && $n->byRef
                && $n->value instanceof Expr\Variable
                && $n->value->name === $var,
        ) !== [];
    }

    /** @param Node[] $nodes */
    private function isInClosure(string $var, array $nodes): bool
    {
        foreach ($this->walkScope($nodes, static fn (Node $n): bool => $n instanceof Expr\ArrowFunction) as $fn) {
            assert($fn instanceof Expr\ArrowFunction);
            if ($this->finder->find([$fn->expr], static fn (Node $n): bool => $n instanceof Expr\Variable && $n->name === $var) !== []) {
                return true;
            }
        }

        foreach ($this->walkScope($nodes, static fn (Node $n): bool => $n instanceof Expr\Closure) as $closure) {
            assert($closure instanceof Expr\Closure);
            foreach ($closure->uses as $use) {
                if ($use->var instanceof Expr\Variable && $use->var->name === $var) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @param Node[] $nodes */
    private function isObjectMutated(string $var, array $nodes): bool
    {
        return $this->walkScope(
            $nodes,
            static fn (Node $n): bool => $n instanceof Expr\Assign
                && $n->var instanceof Expr\PropertyFetch
                && $n->var->var instanceof Expr\Variable
                && $n->var->var->name === $var,
        ) !== [];
    }

    /** @param Node[] $nodes */
    private function isSourceUnset(string $source, array $nodes): bool
    {
        foreach ($this->walkScope($nodes, static fn (Node $n): bool => $n instanceof Stmt\Unset_) as $unset) {
            assert($unset instanceof Stmt\Unset_);
            foreach ($unset->vars as $unsetVar) {
                if ($unsetVar instanceof Expr\Variable && $unsetVar->name === $source) {
                    return true;
                }
            }
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Scope-aware node walker
    // -------------------------------------------------------------------------

    /**
     * Find nodes matching $predicate without crossing scope boundaries
     * (closures, arrow functions, nested functions / methods).
     *
     * Scope-boundary nodes ARE tested against the predicate themselves but
     * their children are NOT traversed.
     *
     * @param  Node[]   $nodes
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

            if ($this->isScopeBoundary($node)) {
                continue;
            }

            foreach ($node->getSubNodeNames() as $subName) {
                /** @phpstan-ignore property.dynamicName */
                $sub = $node->$subName;

                if (is_array($sub)) {
                    array_push($found, ...$this->walkScope($sub, $predicate));
                } elseif ($sub instanceof Node) {
                    array_push($found, ...$this->walkScope([$sub], $predicate));
                }
            }
        }

        return $found;
    }

    private function isScopeBoundary(Node $node): bool
    {
        return $node instanceof Stmt\Function_
            || $node instanceof Stmt\ClassMethod
            || $node instanceof Expr\Closure
            || $node instanceof Expr\ArrowFunction;
    }
}
