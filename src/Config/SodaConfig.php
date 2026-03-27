<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use Bunnivo\Soda\Quality\Config\RuleSections;
use Bunnivo\Soda\Quality\ConfigException;
use Bunnivo\Soda\Quality\Rule\RuleChecker;

/**
 * PHP quality thresholds DSL (callable entrypoint); merges into the same internal shape as nested `rules` payloads.
 *
 * @phpstan-type RulesPayload array{rules?: array<string, array<string, mixed>>}
 */
final class SodaConfig
{
    private readonly StructuralConfig $structural;

    private readonly ComplexityConfig $complexity;

    private readonly BreathingConfig $breathing;

    private readonly NamingConfig $naming;

    /**
     * @var list<string>
     */
    private array $disabledRuleIds = [];

    /**
     * @var list<class-string<SodaPlugin>>
     */
    private array $plugins = [];

    /**
     * @var list<class-string<RuleChecker>>
     */
    private array $extraRules = [];

    private bool $withoutBuiltins = false;

    public function __construct()
    {
        $this->structural = new StructuralConfig;
        $this->complexity = new ComplexityConfig;
        $this->breathing = new BreathingConfig;
        $this->naming = new NamingConfig;
    }

    public function structural(): StructuralConfig
    {
        return $this->structural;
    }

    public function complexity(): ComplexityConfig
    {
        return $this->complexity;
    }

    public function breathing(): BreathingConfig
    {
        return $this->breathing;
    }

    public function naming(): NamingConfig
    {
        return $this->naming;
    }

    public function disableRule(string $ruleId): self
    {
        if ($ruleId === '') {
            throw new \InvalidArgumentException('Rule id must be non-empty.');
        }

        $this->disabledRuleIds[] = $ruleId;

        return $this;
    }

    /**
     * Disable all built-in rules so only explicitly registered plugins/rules run.
     *
     * Use this when you want full control over which rule groups are active.
     * Built-in groups can then be selectively re-added via {@see plugin()}.
     *
     * @example Start from scratch — only your rules:
     *
     *   $config->withoutBuiltins()->rule(MyRule::class);
     *
     * @example Cherry-pick built-in groups:
     *
     *   $config->withoutBuiltins()
     *          ->plugin(StructuralPlugin::class)
     *          ->plugin(ComplexityPlugin::class)
     *          ->rule(MyRule::class);
     */
    public function withoutBuiltins(): self
    {
        $this->withoutBuiltins = true;

        return $this;
    }

    public function isWithoutBuiltins(): bool
    {
        return $this->withoutBuiltins;
    }

    /**
     * Register a plugin by its class name.
     *
     * The plugin will be instantiated once when {@see pluginCheckers()} is called.
     *
     * @param class-string<SodaPlugin> $pluginClass
     *
     * @throws \InvalidArgumentException when the class does not exist or does not implement {@see SodaPlugin}
     */
    public function plugin(string $pluginClass): self
    {
        if ($pluginClass === '') {
            throw new \InvalidArgumentException('Plugin class name must be non-empty.');
        }

        if (! class_exists($pluginClass)) {
            throw new \InvalidArgumentException(sprintf('Plugin class not found: %s', $pluginClass));
        }

        if (! is_a($pluginClass, SodaPlugin::class, true)) {
            throw new \InvalidArgumentException(
                sprintf('%s must implement %s.', $pluginClass, SodaPlugin::class)
            );
        }

        $this->plugins[] = $pluginClass;

        return $this;
    }

    /**
     * Register a single rule checker directly — no plugin wrapper needed.
     *
     * This is the simplest way to add a custom rule. Extend {@see SodaRule} for
     * a convenient base class with built-in helpers.
     *
     * @param class-string<RuleChecker> $ruleClass
     *
     * @throws \InvalidArgumentException when the class does not exist or does not implement {@see RuleChecker}
     *
     * @example
     *   $config->rule(MyCustomRule::class);
     */
    public function rule(string $ruleClass): self
    {
        if ($ruleClass === '') {
            throw new \InvalidArgumentException('Rule class name must be non-empty.');
        }

        if (! class_exists($ruleClass)) {
            throw new \InvalidArgumentException(sprintf('Rule class not found: %s', $ruleClass));
        }

        if (! is_a($ruleClass, RuleChecker::class, true)) {
            throw new \InvalidArgumentException(
                sprintf('%s must implement %s.', $ruleClass, RuleChecker::class)
            );
        }

        $this->extraRules[] = $ruleClass;

        return $this;
    }

    /**
     * Instantiate all registered plugins and inline rules, and collect their checkers.
     *
     * @return list<RuleChecker>
     *
     * @throws ConfigException
     */
    public function pluginCheckers(): array
    {
        $checkers = [];

        foreach ($this->plugins as $pluginClass) {
            $plugin = new $pluginClass;
            array_push($checkers, ...$plugin->checkers());
        }

        foreach ($this->extraRules as $ruleClass) {
            $checkers[] = new $ruleClass;
        }

        return $checkers;
    }

    /**
     * @return RulesPayload
     */
    public function toArray(): array
    {
        /** @var array<string, array<string, mixed>> $rules */
        $rules = [];

        foreach (RuleSections::sectionNames() as $section) {
            $block = $this->sectionConfig($section)->toSectionArray();

            if ($block !== []) {
                $rules[$section] = $block;
            }
        }

        $map = RuleSections::ruleToSection();

        foreach ($this->disabledRuleIds as $ruleId) {
            $section = $map[$ruleId] ?? null;

            if ($section === null) {
                continue;
            }

            $rules[$section] ??= [];
            $rules[$section][$ruleId] = null;
        }

        return ['rules' => $rules];
    }

    private function sectionConfig(string $section): RuleSectionConfig
    {
        return match ($section) {
            RuleSections::STRUCTURAL   => $this->structural,
            RuleSections::COMPLEXITY   => $this->complexity,
            RuleSections::BREATHING    => $this->breathing,
            RuleSections::NAMING       => $this->naming,
            default                    => throw new \InvalidArgumentException('Unknown section: '.$section),
        };
    }
}
