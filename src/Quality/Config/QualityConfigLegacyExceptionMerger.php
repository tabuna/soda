<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

use function is_array;

/**
 * @internal
 */
final class QualityConfigLegacyExceptionMerger
{
    private const string BOOLEAN_METHOD_PREFIX_EXCEPTIONS_KEY = 'boolean_method_prefix_exceptions';

    private const array RULE_EXCEPTION_SCOPES = ['files', 'classes', 'methods'];

    /**
     * @param array<string, array{files: list<string>, classes: list<string>, methods: list<string>}> $ruleExceptions
     * @param array<array-key, mixed>                                                                 $raw
     *
     * @return array<string, array{files: list<string>, classes: list<string>, methods: list<string>}>
     */
    public static function merge(array $ruleExceptions, array $raw): array
    {
        $legacyExceptions = self::extractLegacyBooleanMethodPrefixExceptions($raw);

        if ($legacyExceptions === []) {
            return $ruleExceptions;
        }

        $existing = $ruleExceptions['boolean_methods_without_prefix'] ?? self::emptyRuleExceptions();
        $existing['methods'] = array_values(array_unique([...$existing['methods'], ...$legacyExceptions]));
        $ruleExceptions['boolean_methods_without_prefix'] = $existing;

        return $ruleExceptions;
    }

    /**
     * @return array{files: list<string>, classes: list<string>, methods: list<string>}
     */
    public static function emptyRuleExceptions(): array
    {
        return [
            'files'   => [],
            'classes' => [],
            'methods' => [],
        ];
    }

    /**
     * @return array{files: list<string>, classes: list<string>, methods: list<string>}
     */
    public static function normalizeRuleExceptions(mixed $raw): array
    {
        if (! is_array($raw)) {
            return self::emptyRuleExceptions();
        }

        $normalized = self::emptyRuleExceptions();

        foreach (self::RULE_EXCEPTION_SCOPES as $scope) {
            $values = $raw[$scope] ?? [];

            if (! is_array($values)) {
                continue;
            }

            $normalized[$scope] = array_values(array_unique(array_filter(
                $values,
                static fn (mixed $value): bool => is_string($value) && $value !== '',
            )));
        }

        return $normalized;
    }

    /**
     * @param array<array-key, mixed> $rules
     *
     * @return list<string>
     */
    private static function extractLegacyBooleanMethodPrefixExceptions(array $rules): array
    {
        $naming = $rules[RuleSections::NAMING] ?? null;

        if (! is_array($naming)) {
            return [];
        }

        $raw = $naming[self::BOOLEAN_METHOD_PREFIX_EXCEPTIONS_KEY] ?? [];

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            $raw,
            static fn (mixed $value): bool => is_string($value) && $value !== '',
        )));
    }
}
