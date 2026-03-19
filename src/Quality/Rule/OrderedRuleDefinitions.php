<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * Builds the ordered list of built-in {@see RuleDefinition} entries.
 */
final class OrderedRuleDefinitions
{
    /**
     * @return list<RuleDefinition>
     */
    public static function all(): array
    {
        $sectionStructural = 'structural';

        $sectionComplexity = 'complexity';

        $sectionBreathing = 'breathing';

        $sectionNaming = 'naming';

        $rulesStructural = StructuralRuleDefinitions::all($sectionStructural);

        $rulesComplexity = ComplexityRuleDefinitions::all($sectionComplexity);

        $rulesBreathing = BreathingRuleDefinitions::all($sectionBreathing);

        $rulesNaming = NamingRuleDefinitions::all($sectionNaming);

        return [
            ...$rulesStructural,

            ...$rulesComplexity,

            ...$rulesBreathing,

            ...$rulesNaming,
        ];
    }
}
