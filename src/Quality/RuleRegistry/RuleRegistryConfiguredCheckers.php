<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleRegistry;

use Bunnivo\Soda\Quality\QualityConfig;
use Bunnivo\Soda\Quality\Rule\ClassRules;
use Bunnivo\Soda\Quality\Rule\MethodRules;
use Bunnivo\Soda\Quality\Rule\RuleChecker;

/**
 * @internal kept for backwards-compatibility; use {@see \Bunnivo\Soda\Plugins\StandardPlugin} instead.
 */
final class RuleRegistryConfiguredCheckers
{
    /**
     * @return list<RuleChecker>
     */
    public static function all(QualityConfig $config): array
    {
        return [
            new ClassRules,
            new MethodRules,
        ];
    }
}
