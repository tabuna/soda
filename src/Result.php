<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

final readonly class Result
{
    /**
     * @param list<non-empty-string> $errors
     */
    public function __construct(
        private array $errors,
        private CoreMetrics $core,
        private ?ExtendedMetrics $extended = null,
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
        return $this->core->loc();
    }

    public function complexity(): ComplexityMetrics
    {
        return $this->core->complexity();
    }

    public function classesOrTraits(): int
    {
        return $this->core->complexity()->methods()['classesOrTraits'];
    }

    public function structure(): ?Structure\Metrics
    {
        return $this->extended?->structure();
    }

    public function breathing(): ?Breathing\BreathingMetrics
    {
        return $this->extended?->breathing();
    }
}
