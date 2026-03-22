<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Report\OccurrenceViolationFactory;
use Bunnivo\Soda\Quality\Report\Violation;
use Bunnivo\Soda\Quality\RuleNaming\BooleanMethodPrefixInspector;
use Illuminate\Support\Collection;

final class BooleanMethodPrefixChecker implements RuleChecker
{
    public const string RULE = 'boolean_methods_without_prefix';

    /**
     * @return Collection<int, Violation>
     */
    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        if (! $context->config->isRuleEnabled(self::RULE)) {
            return collect();
        }

        $inspector = BooleanMethodPrefixInspector::fromContext($context);
        $violations = collect();

        foreach ($context->fileMetrics->qualityMetrics() as $file => $metrics) {
            foreach ($inspector->violationsForFile($file, $metrics) as $index => $methodData) {
                if ($index < $inspector->threshold()) {
                    continue;
                }

                $violations->push(OccurrenceViolationFactory::build([
                    'rule'      => self::RULE,
                    'file'      => $file,
                    'value'     => $index + 1,
                    'threshold' => $inspector->threshold(),
                    'line'      => $methodData['line'] ?? null,
                    'message'   => sprintf(
                        'Boolean-returning method %s() should start with is/has/should',
                        $methodData['methodName'] ?? 'method',
                    ),
                    'class'     => $methodData['class'] ?? null,
                    'method'    => $methodData['name'] ?? null,
                ]));
            }
        }

        return $violations;
    }
}
