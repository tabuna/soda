<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Structure;

use function array_pop;

use Bunnivo\Soda\Visitor\NullableReturnVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;

/**
 * Collects structure metrics, dependencies, and LLOC breakdown via AST.
 *
 * @internal
 */
final class MetricsVisitor extends NullableReturnVisitor
{
    private readonly MetricsState $state;

    public function __construct()
    {
        $this->state = new MetricsState();
    }

    protected function doEnterNode(Node $node): void
    {
        $this->dispatchEnter($node);
    }

    protected function doLeaveNode(Node $node): void
    {
        if ($node instanceof Class_ || $node instanceof Trait_) {
            if ($this->state->classStack === []) {
                return;
            }

            array_pop($this->state->classStack);
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
        if (StructureNodeHandlers::handleNamespace($this->state, $node)) {
            return true;
        }

        if (StructureNodeHandlers::handleTypeDeclarations($this->state, $node)) {
            return true;
        }

        if (StructureNodeHandlers::handleMembers($this->state, $node)) {
            return true;
        }

        return StructureNodeHandlers::handleGlobals($this->state, $node);
    }

    private function handleExpressionNodes(Node $node): bool
    {
        return ExpressionHandlers::handle($this->state, $node);
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
