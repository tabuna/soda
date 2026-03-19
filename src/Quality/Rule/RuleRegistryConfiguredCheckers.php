<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\ClassChecker;
use Bunnivo\Soda\Quality\MethodChecker;
use Bunnivo\Soda\Quality\QualityConfig;

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
