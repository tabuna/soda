<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

final readonly class ComplexityMetrics
{
    /**
     * @param array{
     *   functions: int,
     *   funcLowest: int,
     *   funcAverage: float,
     *   funcHighest: int,
     *   classesOrTraits: int,
     *   methods: int,
     *   methodLowest: int,
     *   methodAverage: float,
     *   methodHighest: int,
     *   classLowest?: float,
     *   classAverage?: float,
     *   classHighest?: float,
     *   averagePerLloc?: float,
     * } $data
     */
    public function __construct(private array $data) {}

    /**
     * @return array{count: int, lowest: int, average: float, highest: int}
     */
    public function functions(): array
    {
        return [
            'count'   => $this->data['functions'],
            'lowest'  => $this->data['funcLowest'],
            'average' => $this->data['funcAverage'],
            'highest' => $this->data['funcHighest'],
        ];
    }

    /**
     * @return array{classesOrTraits: int, methods: int, lowest: int, average: float, highest: int}
     */
    public function methods(): array
    {
        return [
            'classesOrTraits' => $this->data['classesOrTraits'],
            'methods'         => $this->data['methods'],
            'lowest'          => $this->data['methodLowest'],
            'average'         => $this->data['methodAverage'],
            'highest'         => $this->data['methodHighest'],
        ];
    }

    /**
     * @return array{lowest: float, average: float, highest: float}
     */
    public function classes(): array
    {
        return [
            'lowest'  => $this->data['classLowest'] ?? 0.0,
            'average' => $this->data['classAverage'] ?? 0.0,
            'highest' => $this->data['classHighest'] ?? 0.0,
        ];
    }

    public function averagePerLloc(): float
    {
        return $this->data['averagePerLloc'] ?? 0.0;
    }
}
