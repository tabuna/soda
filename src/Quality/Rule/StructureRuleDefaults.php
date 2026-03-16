<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

/**
 * @internal
 */
final readonly class StructureRuleDefaults implements RuleDefaultsProvider
{
    public function defaults(): array
    {
        return array_merge(
            (new LengthRuleDefaults())->defaults(),
            (new CountRuleDefaults())->defaults(),
        );
    }
}
