<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use Bunnivo\Soda\Quality\Config\RuleSections;
use InvalidArgumentException;

use function is_array;

/**
 * Applies nested section → rule payloads (legacy merge shape) onto {@see SodaConfig}.
 */
final class NestedRulesApplier
{
    /**
     * @param array<string, array<string, mixed>> $sections
     */
    public static function apply(SodaConfig $config, array $sections): void
    {
        foreach (RuleSections::sectionNames() as $section) {
            $block = $sections[$section] ?? [];

            if ($block === []) {
                continue;
            }

            if (! is_array($block)) {
                throw new InvalidArgumentException(sprintf('Section "%s" must be an array.', $section));
            }

            foreach ($block as $ruleId => $value) {
                if (! is_string($ruleId)) {
                    throw new InvalidArgumentException('Rule id must be a string.');
                }

                self::applyTuple($config, [$section, $ruleId, $value]);
            }
        }
    }

    /**
     * @param array{0: string, 1: string, 2: mixed} $tuple
     */
    private static function applyTuple(SodaConfig $config, array $tuple): void
    {
        [$section, $ruleId, $value] = $tuple;

        if ($value === null) {
            $config->disableRule($ruleId);

            return;
        }

        match ($section) {
            RuleSections::STRUCTURAL   => NestedRulesStructuralApplier::apply($config, $ruleId, $value),
            RuleSections::COMPLEXITY   => NestedRulesMetricsApplier::applyComplexity($config, $ruleId, $value),
            RuleSections::BREATHING    => NestedRulesMetricsApplier::applyBreathing($config, $ruleId, $value),
            RuleSections::NAMING       => NestedRulesNamingApplier::apply($config, $ruleId, $value),
            default                    => throw new InvalidArgumentException('Unknown section: '.$section),
        };
    }
}
