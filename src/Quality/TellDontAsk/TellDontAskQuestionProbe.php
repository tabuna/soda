<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\TellDontAsk;

use function array_key_exists;
use function array_reverse;
use function is_string;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;

/**
 * @internal
 */
final readonly class TellDontAskQuestionProbe
{
    public function __construct(
        private TellDontAskReceiverFingerprint $fingerprint,
    ) {}

    /**
     * @param list<array<string, list<array{receiver: string, method: string}>>> $questionAliases
     *
     * @return list<array{receiver: string, method: string}>
     */
    public function questions(Expr $expr, array $questionAliases): array
    {
        $questions = [];
        $exprType = $expr->getType();

        if ($exprType === 'Expr_Variable' && $this->isNamedVariable($expr)) {
            /** @var Variable $expr */
            $questions = $this->aliasedQuestions('$'.$expr->name, $questionAliases);
        } elseif ($this->isCallExpressionType($exprType)) {
            /** @var MethodCall|NullsafeMethodCall|StaticCall $expr */
            $questions = $this->questionFromCall($expr);
        } elseif ($exprType === 'Expr_BooleanNot') {
            /** @var BooleanNot $expr */
            $questions = $this->questions($expr->expr, $questionAliases);
        } elseif ($this->isLogicalBinaryOpType($exprType)) {
            /** @var BinaryOp $expr */
            $questions = [...$this->questions($expr->left, $questionAliases), ...$this->questions($expr->right, $questionAliases)];
        }

        return $questions;
    }

    /**
     * @param list<array<string, list<array{receiver: string, method: string}>>> $questionAliases
     *
     * @return list<array{receiver: string, method: string}>
     */
    private function aliasedQuestions(string $variable, array $questionAliases): array
    {
        foreach (array_reverse($questionAliases, true) as $aliases) {
            if (array_key_exists($variable, $aliases)) {
                return $aliases[$variable];
            }
        }

        return [];
    }

    /**
     * @return list<array{receiver: string, method: string}>
     */
    private function questionFromCall(MethodCall|NullsafeMethodCall|StaticCall $expr): array
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

    private function isCallExpressionType(string $exprType): bool
    {
        return in_array($exprType, ['Expr_MethodCall', 'Expr_NullsafeMethodCall', 'Expr_StaticCall'], true);
    }

    private function isLogicalBinaryOpType(string $exprType): bool
    {
        return in_array($exprType, ['Expr_BinaryOp_BooleanAnd', 'Expr_BinaryOp_BooleanOr', 'Expr_BinaryOp_LogicalAnd', 'Expr_BinaryOp_LogicalOr', 'Expr_BinaryOp_LogicalXor'], true);
    }

    private function isNamedVariable(Expr $expr): bool
    {
        return $expr instanceof Variable && is_string($expr->name);
    }
}
