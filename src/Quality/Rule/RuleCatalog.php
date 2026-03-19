<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * Single source of truth for rule ids, default thresholds, presentation metadata, and config sections.
 */
final class RuleCatalog
{
    /**
     * @return array<string, RuleDefinition>
     */
    public static function definitions(): array
    {
        $byId = [];

        foreach (self::orderedDefinitions() as $definition) {
            $byId[$definition->fields->identity->id] = $definition;
        }

        return $byId;
    }

    /**
     * Section → ordered rule ids (matches historical soda.json layout).
     *
     * @return array<string, list<string>>
     */
    public static function sectionsOrdered(): array
    {
        $out = [];

        foreach (self::orderedDefinitions() as $definition) {
            $identity = $definition->fields->identity;
            $out[$identity->section] ??= [];
            $out[$identity->section][] = $identity->id;
        }

        return $out;
    }

    /**
     * @return array<string, int|float>
     */
    public static function defaultThresholds(): array
    {
        $thresholds = [];

        foreach (self::definitions() as $id => $definition) {
            $thresholds[$id] = $definition->fields->scoring->defaultThreshold;
        }

        return $thresholds;
    }

    /**
     * @return array<string, array{severity: 'error'|'warning', label: string, comparison?: 'min'|'max'}>
     */
    public static function metadataMap(): array
    {
        $map = [];

        foreach (self::definitions() as $id => $definition) {
            $map[$id] = $definition->toMetadataEntry();
        }

        return $map;
    }

    /**
     * @param list<string> $ruleIds
     *
     * @return array<string, array{severity: 'error'|'warning', label: string, comparison?: 'min'|'max'}>
     */
    public static function metadataForRules(array $ruleIds): array
    {
        $all = self::metadataMap();

        $slice = [];

        foreach ($ruleIds as $id) {
            if (isset($all[$id])) {
                $slice[$id] = $all[$id];
            }
        }

        return $slice;
    }

    /**
     * @return list<RuleDefinition>
     */
    private static function orderedDefinitions(): array
    {
        return OrderedRuleDefinitions::all();
    }
}
