<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

use Bunnivo\Soda\Quality\RuleCatalog\RuleCatalog;

/**
 * Internal rule parser for nested `rules` threshold and exception sections.
 *
 * @internal
 */
final class QualityConfigRuleParser
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array{
     *   0: array<string, int|float>,
     *   1: list<string>,
     *   2: array<string, array{files: list<string>, classes: list<string>, methods: list<string>}>,
     *   3: array<string, array<string, mixed>>
     * }
     */
    public static function mergeRules(array $data): array
    {
        /** @var array<array-key, mixed> $raw */
        $raw = $data['rules'] ?? [];
        [$flattened, $disabled, $ruleExceptions, $ruleOptions] = QualityConfigRuleFlattener::flatten($raw);
        $baseline = RuleCatalog::defaultThresholds();
        $merged = array_merge($baseline, QualityConfigRuleFlattener::validThresholds($flattened));

        return [$merged, $disabled, QualityConfigLegacyExceptionMerger::merge($ruleExceptions, $raw), $ruleOptions];
    }

    /**
     * @return array{files: list<string>, classes: list<string>, methods: list<string>}
     */
    public static function emptyRuleExceptions(): array
    {
        return QualityConfigLegacyExceptionMerger::emptyRuleExceptions();
    }
}
