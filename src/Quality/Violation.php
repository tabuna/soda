<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

final readonly class Violation
{
    public function __construct(
        public string $rule,
        public string $file,
        public ViolationContext $context,
    ) {}

    public function method(): ?string
    {
        return $this->context->method();
    }

    public function class(): ?string
    {
        return $this->context->class();
    }

    public function line(): ?int
    {
        return $this->context->line();
    }

    /**
     * @return array{value: int, threshold: int}
     */
    public function limits(): array
    {
        return ['value' => $this->context->value(), 'threshold' => $this->context->threshold()];
    }

    /**
     * @psalm-return array{rule: string, file: string, method: string|null, class: string|null, value: int, threshold: int}
     */
    public function toArray(): array
    {
        return [
            'rule'      => $this->rule,
            'file'      => $this->file,
            'method'    => $this->context->method(),
            'class'     => $this->context->class(),
            'value'     => $this->limits()['value'],
            'threshold' => $this->limits()['threshold'],
        ];
    }
}
