<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

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
