<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleDefaults;

use Bunnivo\Soda\Quality\RuleCatalog\RuleDefaultsProvider;

/**
 * @internal
 */
final readonly class BreathingRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return array_merge(
            (new BreathingMinRuleDefaults())->defaults(),
            (new BreathingMaxRuleDefaults())->defaults(),
        );
    }
}
