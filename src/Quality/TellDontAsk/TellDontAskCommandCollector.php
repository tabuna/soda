<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\TellDontAsk;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;

/**
 * @internal
 */
final readonly class TellDontAskCommandCollector
{
    public function __construct(
        private TellDontAskReceiverFingerprint $fingerprint,
    ) {}

    /**
     * @param list<Node> $statements
     *
     * @return list<array{receiver: string, method: string}>
     */
    public function fromStatements(array $statements): array
    {
        $commands = [];

        foreach ($statements as $statement) {
            if ($statement instanceof Expression) {
                $commands = [...$commands, ...$this->fromExpr($statement->expr)];

                continue;
            }

            if (! $statement instanceof If_) {
                continue;
            }

            $commands = [...$commands, ...$this->fromStatements($statement->stmts)];

            foreach ($statement->elseifs as $elseif) {
                $commands = [...$commands, ...$this->fromStatements($elseif->stmts)];
            }

            $elseStatements = $statement->else?->stmts ?? [];

            if ($elseStatements !== []) {
                $commands = [...$commands, ...$this->fromStatements($elseStatements)];
            }
        }

        return $commands;
    }

    /**
     * @return list<array{receiver: string, method: string}>
     */
    public function fromExpr(Expr $expr): array
    {
        if ($expr instanceof MethodCall || $expr instanceof NullsafeMethodCall || $expr instanceof StaticCall) {
            return $this->commandFromCall($expr);
        }

        if ($expr instanceof Ternary) {
            return [
                ...$this->fromExpr($expr->if ?? $expr->cond),
                ...$this->fromExpr($expr->else),
            ];
        }

        if ($expr instanceof BinaryOp\BooleanAnd || $expr instanceof BinaryOp\LogicalAnd) {
            return $this->fromExpr($expr->right);
        }

        return [];
    }

    /**
     * @return list<array{receiver: string, method: string}>
     */
    private function commandFromCall(MethodCall|NullsafeMethodCall|StaticCall $expr): array
    {
        $receiver = $this->fingerprint->forCall($expr);
        $method = $this->fingerprint->callName($expr);

        if ($receiver === null || $method === null) {
            return [];
        }

        return [[
            'receiver' => $receiver,
            'method'   => $method,
        ]];
    }
}
