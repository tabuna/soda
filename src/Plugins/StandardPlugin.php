<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Plugins;

use Bunnivo\Soda\Config\SodaPlugin;
use Bunnivo\Soda\Quality\Rule\RuleChecker;

/**
 * All built-in Soda rules in one plugin.
 *
 * Equivalent to registering StructuralPlugin + ComplexityPlugin +
 * BreathingPlugin + NamingPlugin together.
 *
 * @example Explicit full setup in soda.php:
 *
 *   $config->withoutBuiltins()
 *          ->plugin(StandardPlugin::class)
 *          ->rule(MyCustomRule::class);
 * @example Start from scratch — only your rules:
 *
 *   $config->withoutBuiltins()
 *          ->rule(MyCustomRule::class);
 */
final class StandardPlugin implements SodaPlugin
{
    /**
     * @return list<RuleChecker>
     */
    #[\Override]
    public function checkers(): array
    {
        return array_merge(
            (new StructuralPlugin)->checkers(),
            (new ComplexityPlugin)->checkers(),
            (new BreathingPlugin)->checkers(),
            (new NamingPlugin)->checkers(),
        );
    }
}
