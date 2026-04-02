<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use Bunnivo\Soda\Quality\Rule\RuleChecker;

/**
 * Contract for Soda plugins.
 *
 * A plugin bundles one or more {@see RuleChecker} implementations into a
 * reusable, self-contained unit that can be registered in `soda.php` via
 * {@see SodaConfig::plugin()}.
 *
 * @example Creating a plugin:
 *
 *   final class MyPlugin implements SodaPlugin
 *   {
 *       public function checkers(): array
 *       {
 *           return [new MyFirstRule(), new MySecondRule()];
 *       }
 *   }
 * @example Registering in soda.php:
 *
 *   $config->plugin(MyPlugin::class);
 */
interface SodaPlugin
{
    /**
     * Return the rule checkers provided by this plugin.
     *
     * @return list<RuleChecker>
     */
    public function checkers(): array;
}
