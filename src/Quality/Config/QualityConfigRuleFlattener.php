<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

use function is_array;

/**
 * @internal
 */
final class QualityConfigRuleFlattener
{
    /**
     * @param array<array-key, mixed> $rules
     *
     * @return array{
     *   0: array<string, int|float>,
     *   1: list<string>,
     *   2: array<string, array{files: list<string>, classes: list<string>, methods: list<string>}>,
     *   3: array<string, array<string, mixed>>
     * }
     */
    public static function flatten(array $rules): array
    {
        $scratch = new RuleFlattenScratch;

        foreach (RuleSections::sectionNames() as $section) {
            $sectionRules = $rules[$section] ?? [];

            if (! is_array($sectionRules)) {
                continue;
            }

            foreach ($sectionRules as $key => $value) {
                QualityConfigRuleEntryApplier::apply($key, $value, $scratch);
            }
        }

        return [
            $scratch->thresholds,
            array_values(array_unique($scratch->disabled)),
            $scratch->exceptions,
            $scratch->options,
        ];
    }

    /**
     * @param array<string, int|float> $flattened
     *
     * @return array<string, int|float>
     */
    public static function validThresholds(array $flattened): array
    {
        return QualityConfigThresholdFilter::validThresholds($flattened);
    }
}
