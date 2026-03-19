<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\ClassChecker;
use Bunnivo\Soda\Quality\MethodChecker;
use Bunnivo\Soda\Quality\QualityConfig;

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
            new LocChecker(),
            new BreathingChecker(),
            new ClassesChecker(),
            new ClassRules(new ClassChecker($config)),
            new MethodRules(new MethodChecker($config)),
            new RedundantNamingChecker(),
            new NamespaceChecker(),
            new ProjectChecker(),
        ];
    }
}
