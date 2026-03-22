<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleDefaults;

use Bunnivo\Soda\Quality\RuleCatalog\RuleDefaultsProvider;

/**
 * @internal
 */
final readonly class CountClassRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return array_merge(
            (new CountClassPartARuleDefaults())->defaults(),
            (new CountClassPartBRuleDefaults())->defaults(),
        );
    }
}
