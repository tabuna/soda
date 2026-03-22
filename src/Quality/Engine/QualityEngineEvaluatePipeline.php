<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Engine;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\EvaluationContext\FileMetrics;
use Bunnivo\Soda\Quality\EvaluationContext\QualityCore;
use Bunnivo\Soda\Quality\QualityConfig;
use Bunnivo\Soda\Quality\QualityResult;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Bunnivo\Soda\Result;

/**
 * @internal
 *
 * @psalm-param array{
 *     config: QualityConfig,
 *     checkers: list<RuleChecker>,
 *     metrics: Result,
 *     input: EvaluateInput
 * } $payload
 */
final class QualityEngineEvaluatePipeline
{
    public static function finish(array $payload): QualityResult
    {
        $config = $payload['config'];

        $checkers = $payload['checkers'];

        $metrics = $payload['metrics'];

        $input = $payload['input'];

        $namespacesAggregated = QualityEngineNamespaceAggregator::aggregate($input->qualityMetrics);
        $core = new QualityCore($input->qualityMetrics, $input->methodMetrics->complexityByMethod());
        $fileMetrics = new FileMetrics($core, $namespacesAggregated, $input->methodMetrics);
        $context = new EvaluationContext($config, $metrics, $fileMetrics);

        $violations = collect($checkers)
            ->flatMap(fn (RuleChecker $checker) => $checker->check($context))
            ->values();

        return new QualityResult($metrics, $violations);
    }
}
