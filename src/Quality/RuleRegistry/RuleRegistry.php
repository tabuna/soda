<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleRegistry;

use Bunnivo\Soda\Plugins\StandardPlugin;
use Bunnivo\Soda\Quality\Rule\RuleChecker;

/**
 * Registry of all quality rule checkers.
 *
 * Built-in rules are provided by {@see StandardPlugin}. Users can replace
 * or extend them by using {@see \Bunnivo\Soda\Config\SodaConfig::plugin()} and
 * {@see \Bunnivo\Soda\Config\SodaConfig::rule()} in their soda.php, optionally
 * combined with {@see \Bunnivo\Soda\Config\SodaConfig::withoutBuiltins()} for
 * full control.
 */
final class RuleRegistry
{
    /**
     * @return list<RuleChecker>
     */
    public static function default(): array
    {
        return (new StandardPlugin)->checkers();
    }
}
