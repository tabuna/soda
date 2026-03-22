<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleRegistry;

use Bunnivo\Soda\Quality\QualityConfig;
use Bunnivo\Soda\Quality\Rule\RuleChecker;

/**
 * Registry of all quality rule checkers.
 */
final class RuleRegistry
{
    /**
     * @return list<RuleChecker>
     */
    public static function default(QualityConfig $config): array
    {
        return [
            ...RuleRegistryBaselineCheckers::all(),
            ...RuleRegistryConfiguredCheckers::all($config),
        ];
    }
}
