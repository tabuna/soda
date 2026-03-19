<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 *
 * @return list<RuleDefinition>
 */
final class BreathingRuleDefinitions
{
    public static function all(string $section): array
    {
        return [
            ...BreathingRuleScoreDefinitions::entries($section),

            ...BreathingRuleReadabilityDefinitions::entries($section),
        ];
    }
}
