<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\Config\QualityConfigRuleParser;
use Bunnivo\Soda\Quality\Config\QualityConfigRuleState;
use Bunnivo\Soda\Quality\RuleCatalog\RuleCatalog;

use function file_get_contents;
use function is_array;
use function json_decode;

use JsonException;

final readonly class QualityConfig
{
    /**
     * @psalm-var array<string, int|float>
     */
    public array $rules;

    /**
     * @param array<string, int|float>|null $rules           `null` = full defaults from {@see RuleCatalog}; `[]` = empty thresholds.
     * @param list<string>                  $disabledRuleIds
     */
    public function __construct(
        ?array $rules = null,
        /**
         * Rule ids explicitly turned off in config (e.g. `"max_method_length": null`).
         */
        public array $disabledRuleIds = [],
        public QualityConfigRuleState $ruleState = new QualityConfigRuleState(),
    ) {
        $this->rules = $rules ?? RuleCatalog::defaultThresholds();
    }

    public function isRuleEnabled(string $ruleId): bool
    {
        return array_key_exists($ruleId, $this->rules) && ! in_array($ruleId, $this->disabledRuleIds, true);
    }

    /**
     * @psalm-param non-empty-string $path
     *
     * @throws ConfigException
     */
    public static function fromFile(string $path): self
    {
        self::assertReadable($path);
        $content = self::readContent($path);
        $data = self::decodeJson($content, $path);
        [$mergedRules, $disabled, $ruleExceptions, $ruleOptions] = self::mergeRules($data);

        return new self($mergedRules, $disabled, new QualityConfigRuleState($ruleExceptions, $ruleOptions));
    }

    /**
     * @throws ConfigException
     */
    private static function assertReadable(string $path): void
    {
        throw_unless(is_readable($path), ConfigException::class, 'Config file not readable: '.$path);
    }

    /**
     * @throws ConfigException
     * @throws \Throwable
     */
    private static function readContent(string $path): string
    {
        $content = file_get_contents($path);

        throw_if(
            $content === false,
            ConfigException::class,
            'Cannot read config file: '.$path
        );

        return $content;
    }

    /**
     * @throws ConfigException
     *
     * @return array<string, mixed>
     */
    private static function decodeJson(string $content, string $path): array
    {
        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new ConfigException(
                sprintf('Invalid JSON in config "%s": %s', $path, $jsonException->getMessage()),
                $jsonException->getCode(),
                $jsonException,
            );
        }

        throw_unless(is_array($data), ConfigException::class, 'Config must be a JSON object');

        /** @var array<string, mixed> $data */
        return $data;
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
