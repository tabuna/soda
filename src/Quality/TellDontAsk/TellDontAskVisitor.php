<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\TellDontAsk;

use Bunnivo\Soda\Quality\Support\MethodVisitorTrait;
use Bunnivo\Soda\Visitor\NullableReturnVisitor;

use function is_string;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Trait_;

/**
 * Finds the "ask, then tell" shape: query the same receiver in a branch condition and then command it.
 *
 * @internal
 */
final class TellDontAskVisitor extends NullableReturnVisitor
{
    use MethodVisitorTrait;

    /**
     * @psalm-var list<array{
     *   line: int,
     *   method: string|null,
     *   class: string|null,
     *   receiver: string,
     *   question: string,
     *   command: string
     * }>
     */
    private array $result = [];

    private ?string $currentMethod = null;

    private readonly TellDontAskQuestionProbe $questionProbe;

    private readonly TellDontAskCommandCollector $commandCollector;

    private readonly TellDontAskAliasRegistry $aliases;

    public function __construct()
    {
        $fingerprint = new TellDontAskReceiverFingerprint;
        $this->aliases = new TellDontAskAliasRegistry;
        $this->questionProbe = new TellDontAskQuestionProbe($fingerprint);
        $this->commandCollector = new TellDontAskCommandCollector($fingerprint);
    }

    #[\Override]
    protected function doEnterNode(Node $node): void
    {
        match ($node->getType()) {
            'Stmt_Class',
            'Stmt_Trait',
            'Stmt_Enum'                => $this->enterClassScope($node),
            'Expr_Closure',
            'Expr_ArrowFunction'       => $this->pushAliasScope(),
            'Stmt_ClassMethod',
            'Stmt_Function'            => $this->enterMethodScope($node),
            'Expr_Assign'              => $this->recordAliasNode($node),
            'Stmt_If'                  => $this->collectFromIfNode($node),
            'Expr_Ternary'             => $this->collectFromTernaryNode($node),
            'Expr_BinaryOp_BooleanAnd',
            'Expr_BinaryOp_LogicalAnd' => $this->collectFromShortCircuitAnd($node),
            default                    => null,
        };
    }

    #[\Override]
    protected function doLeaveNode(Node $node): void
    {
        if (in_array($node->getType(), ['Stmt_Class', 'Stmt_Trait', 'Stmt_Enum'], true)) {
            $this->popClass();
        } elseif ($node->getType() === 'Expr_Closure' || $node->getType() === 'Expr_ArrowFunction') {
            $this->popAliasScope();
        } elseif ($node->getType() === 'Stmt_ClassMethod' || $node->getType() === 'Stmt_Function') {
            $this->currentMethod = null;
            $this->aliases->clear();
        }
    }

    /**
     * @psalm-return list<array{
     *   line: int,
     *   method: string|null,
     *   class: string|null,
     *   receiver: string,
     *   question: string,
     *   command: string
     * }>
     */
    public function result(): array
    {
        return $this->result;
    }

    private function startMethod(Node $node): void
    {
        /** @var ClassMethod|Function_ $node */
        if (! $this->shouldTrackMethod($node)) {
            return;
        }

        $this->currentMethod = $this->resolveMethodName($node);
        $this->aliases->reset();
    }

    private function pushAliasScope(): void
    {
        if ($this->currentMethod === null) {
            return;
        }

        $this->aliases->pushScope();
    }

    private function popAliasScope(): void
    {
        $this->aliases->popScope();
    }

    private function recordAlias(Node $node): void
    {
        /** @var Assign $node */
        if (! $node->var instanceof Expr\Variable || ! is_string($node->var->name) || ! $this->aliases->hasScopes()) {
            return;
        }

        $key = '$'.$node->var->name;
        $questions = $this->questionProbe->questions($node->expr, $this->aliases->all());
        $this->aliases->record($key, $questions);
    }

    private function collectFromIf(Node $node): void
    {
        /** @var If_ $node */
        $falseCommands = [];

        foreach ($node->elseifs as $elseif) {
            $falseCommands = [...$falseCommands, ...$this->commandCollector->fromStatements($elseif->stmts)];
        }

        if ($node->else !== null) {
            $falseCommands = [...$falseCommands, ...$this->commandCollector->fromStatements($node->else->stmts)];
        }

        $this->collectFromConditional(
            $node->cond,
            $node->getStartLine(),
            $this->commandCollector->fromStatements($node->stmts),
            $falseCommands,
        );
    }

    /**
     * @param list<array{receiver: string, method: string}> ...$commandGroups
     */
    private function collectFromConditional(Expr $condition, int $line, array ...$commandGroups): void
    {
        $questions = $this->questionProbe->questions($condition, $this->aliases->all());

        if ($questions === []) {
            return;
        }

        foreach (array_merge([], ...$commandGroups) as $command) {
            $question = TellDontAskBranchMatcher::firstMatch($questions, $command);

            if ($question === null) {
                continue;
            }

            $this->result[] = [
                'line'     => $line,
                'method'   => $this->currentMethod,
                'class'    => TellDontAskBranchMatcher::currentClassName($this->currentMethod),
                'receiver' => $command['receiver'],
                'question' => $question['method'],
                'command'  => $command['method'],
            ];
        }
    }

    private function enterClassScope(Node $node): void
    {
        /** @var Class_|Trait_|Enum_ $node */
        $this->pushClass($node);
    }

    private function enterMethodScope(Node $node): void
    {
        /** @var ClassMethod|Function_ $node */
        $this->startMethod($node);
    }

    private function recordAliasNode(Node $node): void
    {
        /** @var Assign $node */
        $this->recordAlias($node);
    }

    private function collectFromIfNode(Node $node): void
    {
        /** @var If_ $node */
        $this->collectFromIf($node);
    }

    private function collectFromTernaryNode(Node $node): void
    {
        /** @var Ternary $node */
        $this->collectFromConditional(
            $node->cond,
            $node->getStartLine(),
            $this->commandCollector->fromExpr($node->if ?? $node->cond),
            $this->commandCollector->fromExpr($node->else),
        );
    }

    private function collectFromShortCircuitAnd(Node $node): void
    {
        /** @var BooleanAnd|LogicalAnd $node */
        if ($node->getAttribute('parent')?->getType() !== 'Stmt_Expression') {
            return;
        }

        $this->collectFromConditional(
            $node->left,
            $node->getStartLine(),
            $this->commandCollector->fromExpr($node->right),
        );
    }
}
