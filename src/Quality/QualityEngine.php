<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\EvaluationContext\FileMetrics;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Bunnivo\Soda\Quality\Rule\RuleRegistry;
use Bunnivo\Soda\Result;
use Illuminate\Support\Collection;

final class QualityEngine
{
    private const PENALTIES = [
        'max_method_length'                    => 2,
        'max_class_length'                     => 3,
        'max_cyclomatic_complexity'            => 3,
        'max_arguments'                        => 2,
        'max_file_loc'                         => 2,
        'max_methods_per_class'                => 2,
        'max_properties_per_class'             => 2,
        'max_public_methods'                   => 2,
        'max_dependencies'                     => 2,
        'max_classes_per_file'                 => 2,
        'max_namespace_depth'                  => 2,
        'max_classes_per_namespace'            => 2,
        'max_traits_per_class'                 => 2,
        'max_interfaces_per_class'             => 2,
        'max_classes_per_project'              => 3,
        'min_code_breathing_score'             => 2,
        'min_visual_breathing_index'           => 2,
        'min_identifier_readability_score'     => 2,
        'min_code_oxygen_level'                => 2,
        'max_weighted_cognitive_density'       => 2,
        'max_logical_complexity_factor'        => 2,
    ];

    /**
     * @param list<RuleChecker> $checkers
     */
    public function __construct(
        private readonly QualityConfig $config,
        private readonly array $checkers,
    ) {}

    /**
     * @psalm-param array<string, array{
     *   file_loc: int,
     *   classes_count: int,
     *   classes: array<string, array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, traits: int, interfaces: int, namespace: string, namespace_depth: int}>,
     *   methods: array<string, array{loc: int, args: int}>,
     *   namespaces: array<string, int>,
     *   breathing?: array<string, mixed>
     * }> $qualityMetrics
     * @psalm-param array<string, positive-int> $complexityByMethod
     */
    public function evaluate(Result $metrics, array $qualityMetrics, array $complexityByMethod): QualityResult
    {
        $namespacesAggregated = $this->aggregateNamespaces($qualityMetrics);
        $fileMetrics = new FileMetrics($qualityMetrics, $complexityByMethod, $namespacesAggregated);
        $context = new EvaluationContext($this->config, $metrics, $fileMetrics);

        $violations = collect($this->checkers)
            ->flatMap(fn (RuleChecker $checker) => $checker->check($context))
            ->values();
        $score = $this->calculateScore($violations);

        return new QualityResult($metrics, $score, $violations);
    }

    /**
     * @psalm-param array<string, array<string, mixed>> $qualityMetrics
     *
     * @return Collection<string, array{count: int, file: string}>
     */
    private function aggregateNamespaces(array $qualityMetrics): Collection
    {
        /** @var array<string, int> $empty */
        $empty = [];

        return collect($qualityMetrics)
            ->flatMap(function (array $data, string $file) use ($empty): iterable {
                $namespaces = $data['namespaces'] ?? $empty;

                return collect($namespaces)->map(
                    fn (int $count, string $namespace): array => [
                        'ns'    => $namespace,
                        'count' => $count,
                        'file'  => $file,
                    ],
                );
            })
            ->groupBy('ns')
            ->map(function (Collection $group): array {
                $first = $group->first();

                return [
                    'count' => $group->sum('count'),
                    'file'  => is_array($first) && isset($first['file']) ? $first['file'] : '',
                ];
            });
    }

    /**
     * @param Collection<int, Violation> $violations
     */
    private function calculateScore(Collection $violations): int
    {
        $penalty = $violations->sum(fn (Violation $v) => self::PENALTIES[$v->rule] ?? 2);

        return max(0, 100 - $penalty);
    }

    public static function create(QualityConfig $config): self
    {
        return new self($config, RuleRegistry::default($config));
    }
}
