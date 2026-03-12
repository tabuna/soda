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

namespace Bunnivo\Soda;

use Bunnivo\Soda\Structure\Metrics;

final readonly class Result
{
    public function __construct(
        private array $errors,
        private LocMetrics $loc,
        private ComplexityMetrics $complexity,
        private ?Metrics $structure = null,
    ) {}

    /**
     * @return array{hasErrors: bool, errors: list<non-empty-string>}
     */
    public function errorInfo(): array
    {
        return ['hasErrors' => $this->errors !== [], 'errors' => $this->errors];
    }

    public function loc(): LocMetrics
    {
        return $this->loc;
    }

    public function complexity(): ComplexityMetrics
    {
        return $this->complexity;
    }

    public function classesOrTraits(): int
    {
        return $this->complexity->methods()['classesOrTraits'];
    }

    public function structure(): ?Metrics
    {
        return $this->structure;
    }
}
