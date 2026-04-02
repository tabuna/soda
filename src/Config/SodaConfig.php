<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use Bunnivo\Soda\Quality\Rule\RuleChecker;

/**
 * Configuration container for `soda.php`.
 *
 * Start with {@see Soda::configure()} and add rule instances directly:
 *
 * @example
 *   return Soda::configure()
 *       ->withPlugins([
 *           new MaxFileLoc(700),
 *           new MaxMethodLength(100),
 *           new MaxCyclomaticComplexity(10),
 *       ]);
 */
final class SodaConfig
{
    /** @var list<RuleChecker> */
    private array $checkers = [];

    /**
     * Register a plugin by its class name.
     *
     * @param class-string<SodaPlugin> $pluginClass
     *
     * @throws \InvalidArgumentException when the class does not exist or does not implement {@see SodaPlugin}
     */
    public function plugin(string $pluginClass): self
    {
        throw_if($pluginClass === '', \InvalidArgumentException::class, 'Plugin class name must be non-empty.');

        if (! class_exists($pluginClass)) {
            throw new \InvalidArgumentException(sprintf('Plugin class not found: %s', $pluginClass));
        }

        if (! is_a($pluginClass, SodaPlugin::class, true)) {
            throw new \InvalidArgumentException(
                sprintf('%s must implement %s.', $pluginClass, SodaPlugin::class)
            );
        }

        array_push($this->checkers, ...(new $pluginClass)->checkers());

        return $this;
    }

    /**
     * Register a single rule checker by class name.
     *
     * Prefer {@see withPlugins()} with an instance when the rule needs constructor args.
     *
     * @param class-string<RuleChecker> $ruleClass
     *
     * @throws \InvalidArgumentException when the class does not exist or does not implement {@see RuleChecker}
     */
    public function rule(string $ruleClass): self
    {
        throw_if($ruleClass === '', \InvalidArgumentException::class, 'Rule class name must be non-empty.');

        if (! class_exists($ruleClass)) {
            throw new \InvalidArgumentException(sprintf('Rule class not found: %s', $ruleClass));
        }

        if (! is_a($ruleClass, RuleChecker::class, true)) {
            throw new \InvalidArgumentException(
                sprintf('%s must implement %s.', $ruleClass, RuleChecker::class)
            );
        }

        $this->checkers[] = new $ruleClass;

        return $this;
    }

    /**
     * Register rule or plugin **instances** directly.
     *
     * This is the primary API. Pass any mix of {@see SodaPlugin} and
     * {@see RuleChecker} instances. Each rule class carries its own threshold
     * via its constructor — no global config object required.
     *
     * @param array<SodaPlugin|RuleChecker> $plugins
     *
     * @throws \InvalidArgumentException when an element implements neither interface
     *
     * @example
     *   return Soda::configure()
     *       ->withPlugins([
     *           new MaxFileLoc(700),
     *           new MaxCyclomaticComplexity(10),
     *           new UselessVariableRule(),
     *       ]);
     */
    public function withPlugins(array $plugins): self
    {
        foreach ($plugins as $plugin) {
            if ($plugin instanceof SodaPlugin) {
                array_push($this->checkers, ...$plugin->checkers());
            } elseif ($plugin instanceof RuleChecker) {
                $this->checkers[] = $plugin;
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Each entry in withPlugins() must implement %s or %s, got %s.',
                    SodaPlugin::class,
                    RuleChecker::class,
                    get_debug_type($plugin),
                ));
            }
        }

        return $this;
    }

    /**
     * Returns all registered checkers in registration order.
     *
     * @return list<RuleChecker>
     */
    public function pluginCheckers(): array
    {
        return $this->checkers;
    }
}
