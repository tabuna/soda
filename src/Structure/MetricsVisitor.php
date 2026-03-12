<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Bunnivo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda\Structure;

use function array_pop;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Const_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;

/**
 * Collects structure metrics, dependencies, and LLOC breakdown via AST.
 *
 * @internal
 */
final class MetricsVisitor extends NodeVisitorAbstract
{
    private MetricsState $state;

    public function __construct()
    {
        $this->state = new MetricsState();
    }

    #[\Override]
    public function enterNode(Node $node): void
    {
        $this->dispatchEnter($node);
    }

    #[\Override]
    public function leaveNode(Node $node): void
    {
        if ($node instanceof Class_ || $node instanceof Trait_) {
            if ($this->state->classStack !== []) {
                array_pop($this->state->classStack);
            }
        }
    }

    private function dispatchEnter(Node $node): void
    {
        $handled = $this->handleStructureNodes($node)
            || $this->handleExpressionNodes($node);

        if (! $handled && $node instanceof Variable) {
            NodeHandlers::handleVariable($this->state, $node);
        }
    }

    private function handleStructureNodes(Node $node): bool
    {
        if ($node instanceof Namespace_) {
            $name = $node->name?->toString() ?? '';
            if ($name !== '') {
                $this->state->namespaces[$name] = true;
            }

            return true;
        }

        if ($node instanceof Interface_) {
            $this->state->inc('interfaces');

            return true;
        }

        if ($node instanceof Trait_) {
            $this->state->inc('traits');

            return true;
        }

        if ($node instanceof Class_) {
            NodeHandlers::handleClass($this->state, $node);

            return true;
        }

        if ($node instanceof ClassMethod) {
            NodeHandlers::handleClassMethod($this->state, $node);

            return true;
        }

        if ($node instanceof Function_) {
            NodeHandlers::handleFunction($this->state, $node);

            return true;
        }

        if ($node instanceof Closure || $node instanceof ArrowFunction) {
            NodeHandlers::handleAnonymousFunction($this->state, $node);

            return true;
        }

        if ($node instanceof ClassConst) {
            NodeHandlers::handleClassConst($this->state, $node);

            return true;
        }

        if ($node instanceof Const_) {
            $this->state->inc('globalConstants');

            return true;
        }

        if ($node instanceof Global_) {
            $this->state->inc('globalVariableAccesses');

            return true;
        }

        return false;
    }

    private function handleExpressionNodes(Node $node): bool
    {
        if ($node instanceof StaticCall) {
            $this->state->inc('staticMethodCalls');

            return true;
        }

        if ($node instanceof MethodCall) {
            $this->state->inc('nonStaticMethodCalls');

            return true;
        }

        if ($node instanceof StaticPropertyFetch) {
            $this->state->inc('staticAttributeAccesses');

            return true;
        }

        if ($node instanceof PropertyFetch) {
            $this->state->inc('nonStaticAttributeAccesses');

            return true;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function result(): array
    {
        return $this->state->toResult();
    }

    /**
     * @param array<string, array<string, mixed>> $results
     *
     * @return array<string, mixed>
     *
     * @psalm-suppress PossiblyUnusedMethod used by tests and external callers
     */
    public static function merge(array $results): array
    {
        return MetricsMerger::merge($results);
    }

    /**
     * @param array<string, mixed> $stats
     *
     * @return array{llocClasses: int, llocFunctions: int, llocGlobal: int, classLlocMin: int, classLlocAvg: int, classLlocMax: int, methodLlocMin: int, methodLlocAvg: int, methodLlocMax: int, averageMethodsPerClass: int, minimumMethodsPerClass: int, maximumMethodsPerClass: int, averageFunctionLength: int}
     *
     * @psalm-suppress PossiblyUnusedMethod used by tests and external callers
     */
    public static function computeStats(array $stats, int $lloc): array
    {
        return StatsCalculator::compute($stats, $lloc);
    }
}
