<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

/**
 * @internal
 */
final readonly class RuleCheckerState
{
    public function __construct(
        private string $rule,
        private RuleScope $scope,
        private bool $exceededMode,
    ) {}

    public static function create(string $rule, bool $exceededMode): self
    {
        return new self($rule, RuleScope::empty(), $exceededMode);
    }

    public function rule(): string
    {
        return $this->rule;
    }

    public function file(): string
    {
        return $this->scope->file();
    }

    public function context(): RuleContext
    {
        return $this->scope->context();
    }

    public function threshold(): Threshold
    {
        return $this->scope->threshold();
    }

    public function scope(): RuleScope
    {
        return $this->scope;
    }

    public function exceededMode(): bool
    {
        return $this->exceededMode;
    }

    public function withScope(RuleScope $scope): self
    {
        return new self($this->rule, $scope, $this->exceededMode);
    }
}
