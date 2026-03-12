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

use function array_merge;

use Bunnivo\Soda\Quality\EvaluationContext\FileMetrics;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Bunnivo\Soda\Quality\Rule\RuleRegistry;
use Bunnivo\Soda\Result;

final class QualityEngine
{
    private const PENALTIES = [
        'max_method_length'         => 2,
        'max_class_length'          => 3,
        'max_cyclomatic_complexity' => 3,
        'max_arguments'             => 2,
        'max_file_loc'              => 2,
        'max_methods_per_class'     => 2,
        'max_properties_per_class'  => 2,
        'max_public_methods'        => 2,
        'max_dependencies'          => 2,
        'max_classes_per_file'      => 2,
        'max_namespace_depth'       => 2,
        'max_classes_per_namespace' => 2,
        'max_traits_per_class'      => 2,
        'max_interfaces_per_class'  => 2,
        'max_classes_per_project'   => 3,
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
     *   namespaces: array<string, int>
     * }> $qualityMetrics
     * @psalm-param array<string, positive-int> $complexityByMethod
     */
    public function evaluate(Result $metrics, array $qualityMetrics, array $complexityByMethod): QualityResult
    {
        $namespacesAggregated = $this->aggregateNamespaces($qualityMetrics);
        $fileMetrics = new FileMetrics($qualityMetrics, $complexityByMethod, $namespacesAggregated);
        $context = new EvaluationContext($this->config, $metrics, $fileMetrics);

        $violations = [];
        foreach ($this->checkers as $checker) {
            $violations = array_merge($violations, $checker->check($context));
        }

        $score = $this->calculateScore($violations);

        return new QualityResult($metrics, $score, $violations);
    }

    /**
     * @psalm-param array<string, array{namespaces?: array<string, int>}> $qualityMetrics
     *
     * @return array<string, array{count: int, file: string}>
     */
    private function aggregateNamespaces(array $qualityMetrics): array
    {
        $aggregated = [];

        foreach ($qualityMetrics as $file => $data) {
            foreach ($data['namespaces'] ?? [] as $ns => $count) {
                if (! isset($aggregated[$ns])) {
                    $aggregated[$ns] = ['count' => 0, 'file' => $file];
                }
                $aggregated[$ns]['count'] += $count;
            }
        }

        return $aggregated;
    }

    /**
     * @param list<Violation> $violations
     */
    private function calculateScore(array $violations): int
    {
        $penalty = 0;
        foreach ($violations as $v) {
            $penalty += self::PENALTIES[$v->rule] ?? 2;
        }

        return max(0, 100 - $penalty);
    }

    public static function create(QualityConfig $config): self
    {
        return new self($config, RuleRegistry::default($config));
    }
}
