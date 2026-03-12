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
        return $this->context->method;
    }

    public function class(): ?string
    {
        return $this->context->class;
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
            'method'    => $this->context->method,
            'class'     => $this->context->class,
            'value'     => $this->limits()['value'],
            'threshold' => $this->limits()['threshold'],
        ];
    }
}
