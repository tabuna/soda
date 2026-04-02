<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleCatalog;

use Bunnivo\Soda\Quality\RuleBreathing\BreathingRuleDefinitions;
use Bunnivo\Soda\Quality\RuleBreathing\ComplexityRuleDefinitions;
use Bunnivo\Soda\Quality\RuleStructure\NamingRuleDefinitions;
use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleLengthDefinitions;
use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleScopeEfferentAndFileDefinitions;
use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleScopeNamespaceDefinitions;
use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleScopeShapeDefinitions;
use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleSmellDefinitions;
use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleSurfaceApiDefinitions;
use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleSurfaceSizeDefinitions;

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
     * Section → ordered rule ids (matches nested `rules` section layout).
     *
     * @return array<string, list<string>>
     */
    public static function sectionsOrdered(): array
    {
        $out = [];

        foreach (self::orderedDefinitions() as $definition) {
            $identity = $definition->fields->identity;
            $section = $identity->section;
            $list = $out[$section] ?? [];
            $list[] = $identity->id;
            $out[$section] = $list;
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
        $sec = [
            'structural' => 'structural',
            'complexity' => 'complexity',
            'breathing'  => 'breathing',
            'naming'     => 'naming',
        ];

        return [
            ...StructuralRuleLengthDefinitions::entries($sec['structural']),
            ...StructuralRuleSurfaceSizeDefinitions::entries($sec['structural']),
            ...StructuralRuleSurfaceApiDefinitions::entries($sec['structural']),
            ...StructuralRuleSmellDefinitions::entries($sec['structural']),
            ...StructuralRuleScopeEfferentAndFileDefinitions::entries($sec['structural']),
            ...StructuralRuleScopeNamespaceDefinitions::entries($sec['structural']),
            ...StructuralRuleScopeShapeDefinitions::entries($sec['structural']),
            ...ComplexityRuleDefinitions::all($sec['complexity']),
            ...BreathingRuleDefinitions::all($sec['breathing']),
            ...NamingRuleDefinitions::all($sec['naming']),
        ];
    }
}
