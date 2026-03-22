<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleRegistry;

use Bunnivo\Soda\Quality\ClassChecker;
use Bunnivo\Soda\Quality\MethodChecker;
use Bunnivo\Soda\Quality\QualityConfig;
use Bunnivo\Soda\Quality\Rule\ClassRules;
use Bunnivo\Soda\Quality\Rule\MethodRules;
use Bunnivo\Soda\Quality\Rule\RuleChecker;

/**
 * Checkers built with config-bound {@see ClassChecker} / {@see MethodChecker}.
 *
 * @internal
 */
final class RuleRegistryConfiguredCheckers
{
    /**
     * @return list<RuleChecker>
     */
    public static function all(QualityConfig $config): array
    {
        return [
            new ClassRules(new ClassChecker($config)),
            new MethodRules(new MethodChecker($config)),
        ];
    }
}
