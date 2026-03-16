<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

final readonly class BreathingMetrics
{
    private function __construct(
        private BreathingFactors $factors,
        private float $cbs,
    ) {}

    public static function fromFactors(BreathingFactors $factors, float $cbs): self
    {
        return new self($factors, $cbs);
    }

    public function wcd(): float
    {
        return $this->factors->cognitive()->wcd;
    }

    public function lcf(): float
    {
        return $this->factors->cognitive()->lcf;
    }

    public function vbi(): float
    {
        return $this->factors->airiness()->vbi;
    }

    public function irs(): float
    {
        return $this->factors->airiness()->irs;
    }

    public function col(): float
    {
        return $this->factors->airiness()->col;
    }

    public function cbs(): float
    {
        return $this->cbs;
    }

    public function get(string $key): float
    {
        return match ($key) {
            'wcd'   => $this->wcd(),
            'lcf'   => $this->lcf(),
            'vbi'   => $this->vbi(),
            'irs'   => $this->irs(),
            'col'   => $this->col(),
            'cbs'   => $this->cbs(),
            default => throw new \InvalidArgumentException('Unknown metric: '.$key),
        };
    }

    public function toArray(): array
    {
        return [
            'wcd' => round($this->wcd(), 2),
            'lcf' => round($this->lcf(), 2),
            'vbi' => round($this->vbi(), 2),
            'irs' => round($this->irs(), 2),
            'col' => round($this->col(), 2),
            'cbs' => round($this->cbs(), 2),
        ];
    }
}
