<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Quality\Config\QualityConfigRuleParser;
use Bunnivo\Soda\Quality\Config\QualityConfigRuleState;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Bunnivo\Soda\Quality\RuleCatalog\RuleCatalog;

use function is_callable;

final readonly class QualityConfig
{
    /**
     * @psalm-var array<string, int|float>
     */
    public array $rules;

    /**
     * @param array<string, int|float>|null $rules           `null` = full defaults from {@see RuleCatalog}; `[]` = empty thresholds.
     * @param list<string>                  $disabledRuleIds
     * @param list<RuleChecker>             $pluginCheckers  Extra checkers registered via plugins in soda.php.
     */
    public function __construct(
        ?array $rules = null,
        /**
         * Rule ids explicitly turned off in config (e.g. `"max_method_length": null`).
         */
        public array $disabledRuleIds = [],
        public QualityConfigRuleState $ruleState = new QualityConfigRuleState(),
        public array $pluginCheckers = [],
    ) {
        $this->rules = $rules ?? RuleCatalog::defaultThresholds();
    }

    public function isRuleEnabled(string $ruleId): bool
    {
        return array_key_exists($ruleId, $this->rules) && ! in_array($ruleId, $this->disabledRuleIds, true);
    }

    /**
     * @param array<string, mixed> $data Root payload with `rules` sections (tests and tooling); file-based config is {@see self::fromPhpConfiguratorFile()}.
     */
    public static function fromRulesData(array $data): self
    {
        [$mergedRules, $disabled, $ruleExceptions, $ruleOptions] = self::mergeRules($data);

        return new self($mergedRules, $disabled, new QualityConfigRuleState($ruleExceptions, $ruleOptions));
    }

    /**
     * Loads thresholds from a PHP file that returns `callable(SodaConfig): void`.
     *
     * @psalm-param non-empty-string $path
     *
     * @throws ConfigException
     */
    public static function fromPhpConfiguratorFile(string $path): self
    {
        self::assertReadable($path);

        /** @var mixed $export */
        $export = require $path;

        throw_unless(
            is_callable($export),
            ConfigException::class,
            sprintf('PHP config "%s" must return a callable(%s): void.', $path, SodaConfig::class)
        );

        $soda = new SodaConfig;
        $export($soda);
        [$mergedRules, $disabled, $ruleExceptions, $ruleOptions] = self::mergeRules($soda->toArray());

        return new self($mergedRules, $disabled, new QualityConfigRuleState($ruleExceptions, $ruleOptions), $soda->pluginCheckers());
    }

    /**
     * @throws ConfigException
     */
    private static function assertReadable(string $path): void
    {
        throw_unless(is_readable($path), ConfigException::class, 'Config file not readable: '.$path);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array{
     *   0: array<string, int|float>,
     *   1: list<string>,
     *   2: array<string, array{files: list<string>, classes: list<string>, methods: list<string>}>,
     *   3: array<string, array<string, mixed>>
     * }
     */
    private static function mergeRules(array $data): array
    {
        return QualityConfigRuleParser::mergeRules($data);
    }

    public static function default(): self
    {
        return new self;
    }

    /**
     * @psalm-return int|float
     */
    public function getRule(string $key): int|float
    {
        return $this->rules[$key] ?? 0;
    }

    /**
     * @return list<string>
     */
    public function booleanMethodPrefixExceptions(): array
    {
        return $this->ruleExceptions('boolean_methods_without_prefix')['methods'];
    }

    /**
     * @return array{files: list<string>, classes: list<string>, methods: list<string>}
     */
    public function ruleExceptions(string $ruleId): array
    {
        return $this->ruleState->exceptions[$ruleId] ?? QualityConfigRuleParser::emptyRuleExceptions();
    }

    /**
     * @return array<string, mixed>
     */
    public function ruleOptions(string $ruleId): array
    {
        return $this->ruleState->options[$ruleId] ?? [];
    }
}
