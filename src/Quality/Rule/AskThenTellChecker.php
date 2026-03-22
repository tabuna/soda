<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Report\OccurrenceViolationFactory;
use Bunnivo\Soda\Quality\Report\Violation;
use Illuminate\Support\Collection;

use function sprintf;

final class AskThenTellChecker implements RuleChecker
{
    private const string RULE = 'max_ask_then_tell_patterns';

    /**
     * @return Collection<int, Violation>
     */
    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        if (! $context->config->isRuleEnabled(self::RULE)) {
            return collect();
        }

        $threshold = (int) $context->config->getRule(self::RULE);
        $violations = collect();

        foreach ($context->fileMetrics->qualityMetrics() as $file => $metrics) {
            $entries = $metrics['askThenTell'] ?? null;
            if (! is_array($entries)) {
                continue;
            }

            foreach ($entries as $index => $entry) {
                if ($index < $threshold) {
                    continue;
                }

                $violations->push(OccurrenceViolationFactory::build([
                    'rule'      => self::RULE,
                    'file'      => $file,
                    'value'     => $index + 1,
                    'threshold' => $threshold,
                    'line'      => $entry['line'] ?? null,
                    'message'   => sprintf(
                        'Asked %s via %s(), then told it via %s()',
                        $entry['receiver'] ?? 'object',
                        $entry['question'] ?? 'query',
                        $entry['command'] ?? 'command',
                    ),
                    'class'     => $entry['class'] ?? null,
                    'method'    => $entry['method'] ?? null,
                ]));
            }
        }

        return $violations;
    }
}
