<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Limits;
use Bunnivo\Soda\Quality\Naming\RedundantNamingAnalyser;
use Bunnivo\Soda\Quality\Report\Violation;
use Bunnivo\Soda\Quality\Report\ViolationBuilder;
use Illuminate\Support\Collection;

final readonly class RedundantNamingChecker implements RuleChecker
{
    private const string RULE = 'avoid_redundant_naming';

    public function __construct(
        private ?RedundantNamingAnalyser $analyser = null,
    ) {}

    /**
     * @return Collection<int, Violation>
     */
    public function check(EvaluationContext $context): Collection
    {
        if (! $context->config->isRuleEnabled(self::RULE)) {
            return collect([]);
        }

        $threshold = (int) $context->config->getRule(self::RULE);
        if ($threshold <= 0) {
            return collect([]);
        }

        $similarityThreshold = $this->normalisedSimilarityThreshold($threshold);
        $analyser = $this->analyser ?? new RedundantNamingAnalyser($similarityThreshold);

        return $this->violationsFromQualityMetrics($context, $analyser);
    }

    private function normalisedSimilarityThreshold(int $threshold): float
    {
        return $threshold >= 1 && $threshold <= 100 ? (float) $threshold : 80.0;
    }

    private function violationsFromQualityMetrics(EvaluationContext $context, RedundantNamingAnalyser $analyser): Collection
    {
        $violations = collect([]);

        foreach ($context->fileMetrics->qualityMetrics() as $file => $metrics) {
            $naming = $metrics['naming'] ?? null;
            if (! is_array($naming)) {
                continue;
            }

            if (! isset($naming['classes'], $naming['methods'])) {
                continue;
            }

            foreach ($analyser->analyse($naming) as $v) {
                $violations->push($this->toViolation($file, $v));
            }
        }

        return $violations;
    }

    /**
     * @param array{type: string, current: string, suggested: string, reason: string, similarity: float, line: int, className?: string} $v
     */
    private function toViolation(string $file, array $v): Violation
    {
        $similarity = (int) round($v['similarity']);
        $limits = new Limits($similarity, 80);

        $compact = $this->compactMessage($v['current'], $v['suggested']);
        $builder = ViolationBuilder::of(self::RULE, $file, $limits)
            ->atLine($v['line'])
            ->withMessage(sprintf('Redundant naming: %s (%d%%)', $compact, $similarity));

        if ($v['type'] === 'method' && isset($v['className'])) {
            return $this->withMethodContext($builder, $v['className'], $v['current'])->build();
        }

        if ($v['type'] === 'class') {
            return $builder->atClass($v['current'])->build();
        }

        return $builder->build();
    }

    private function withMethodContext(ViolationBuilder $builder, string $className, string $current): ViolationBuilder
    {
        $builder = $builder->atClass($className);
        $methodPart = $current;
        if (str_contains($methodPart, '(')) {
            $methodPart = substr($methodPart, 0, (int) strpos($methodPart, '('));
        }

        return $builder->atMethod($methodPart);
    }

    private function compactMessage(string $current, string $suggested): string
    {
        $cur = str_contains($current, '(') ? substr($current, 0, (int) strpos($current, '(')) : $current;
        $sug = str_contains($suggested, '(') ? substr($suggested, 0, (int) strpos($suggested, '(')) : $suggested;

        return $cur.' → '.$sug;
    }
}
