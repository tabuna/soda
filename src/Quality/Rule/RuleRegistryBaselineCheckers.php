<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\QualityConfig;

/**
 * Checkers that do not need {@see QualityConfig} in the constructor.
 *
 * @internal
 */
final class RuleRegistryBaselineCheckers
{
    /**
     * @return list<RuleChecker>
     */
    public static function all(): array
    {
        return [
            new LocChecker,
            new BreathingChecker,
            new ClassesChecker,
            new RedundantNamingChecker,
            new NamespaceChecker,
            new ProjectChecker,
        ];
    }
}
