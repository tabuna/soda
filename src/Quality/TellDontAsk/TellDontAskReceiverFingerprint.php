<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\TellDontAsk;

use function is_string;
use function method_exists;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;

/**
 * @internal
 */
final class TellDontAskReceiverFingerprint
{
    public function fromExpr(Expr $expr): ?string
    {
        $callFingerprint = $this->callExprFingerprint($expr);

        if ($callFingerprint !== null) {
            return $callFingerprint;
        }

        $valueFingerprint = $this->valueExprFingerprint($expr);

        if ($valueFingerprint !== null) {
            return $valueFingerprint;
        }

        return $expr instanceof ArrayDimFetch ? $this->fromExpr($expr->var) : null;
    }

    public function forCall(MethodCall|NullsafeMethodCall|StaticCall $expr): ?string
    {
        return match (true) {
            $expr instanceof MethodCall || $expr instanceof NullsafeMethodCall => $this->fromExpr($expr->var),
            $expr instanceof StaticCall                                        => $expr->class instanceof Name ? $expr->class->toString() : null,
        };
    }

    public function callName(MethodCall|NullsafeMethodCall|StaticCall $expr): ?string
    {
        return method_exists($expr->name, 'toString') ? $expr->name->toString() : null;
    }

    private function callExprFingerprint(Expr $expr): ?string
    {
        return match (true) {
            $expr instanceof MethodCall || $expr instanceof NullsafeMethodCall => $this->methodReceiverFingerprint($expr),
            $expr instanceof StaticCall                                        => $expr->class instanceof Name ? $expr->class->toString() : null,
            default                                                            => null,
        };
    }

    private function valueExprFingerprint(Expr $expr): ?string
    {
        return match (true) {
            $expr instanceof Variable && is_string($expr->name)  => '$'.$expr->name,
            $expr instanceof PropertyFetch                       => $this->propertyFingerprint($expr),
            $expr instanceof StaticPropertyFetch                 => $this->staticPropertyFingerprint($expr),
            default                                              => null,
        };
    }

    private function methodReceiverFingerprint(MethodCall|NullsafeMethodCall $expr): ?string
    {
        $receiver = $this->fromExpr($expr->var);
        $method = $this->callName($expr);

        if ($receiver === null || $method === null) {
            return null;
        }

        return $receiver.'->'.$method.'()';
    }

    private function propertyFingerprint(PropertyFetch $expr): ?string
    {
        if (! method_exists($expr->name, 'toString')) {
            return null;
        }

        $receiver = $this->fromExpr($expr->var);

        return $receiver !== null ? $receiver.'->'.$expr->name->toString() : null;
    }

    private function staticPropertyFingerprint(StaticPropertyFetch $expr): ?string
    {
        if (! $expr->class instanceof Name || ! method_exists($expr->name, 'toString')) {
            return null;
        }

        return $expr->class->toString().'::$'.$expr->name->toString();
    }
}
