<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Flow;

use PhpParser\Node;
use PhpParser\Node\Expr;

/**
 * @internal
 */
final class ConditionRecorder
{
    /** @var array<string, list<array{line: int, count: int}>> */
    private array $conditionsByMethod = [];

    private ?string $currentMethod = null;

    public function setMethod(?string $method): void
    {
        $this->currentMethod = $method;
    }

    public function initMethod(string $method): void
    {
        $this->conditionsByMethod[$method] = [];
    }

    public function record(Node $node): void
    {
        if ($this->currentMethod === null) {
            return;
        }

        $cond = ConditionExtractor::extract($node);
        if ($cond instanceof Expr) {
            $count = BooleanOperandCounter::count($cond);
            if ($count > 0) {
                $this->conditionsByMethod[$this->currentMethod][] = [
                    'line'  => $cond->getStartLine(),
                    'count' => $count,
                ];
            }
        }
    }

    /**
     * @psalm-return array<string, list<array{line: int, count: int}>>
     */
    public function result(): array
    {
        return $this->conditionsByMethod;
    }
}
