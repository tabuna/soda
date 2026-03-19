<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\Config\RuleFlattenScratch;
use Bunnivo\Soda\Quality\Rule\RuleCatalog;

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
    ) {
        $this->rules = $rules ?? RuleCatalog::defaultThresholds();
    }

    public function isRuleEnabled(string $ruleId): bool
    {
        return ! in_array($ruleId, $this->disabledRuleIds, true);
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
        [$mergedRules, $disabled] = self::mergeRules($data);

        return new self($mergedRules, $disabled);
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
     * @return array{0: array<string, int|float>, 1: list<string>}
     */
    private static function mergeRules(array $data): array
    {
        /** @var array<array-key, mixed> $raw */
        $raw = $data['rules'] ?? [];

        [$flattened, $disabled] = self::flattenRules($raw);
        $filtered = collect($flattened)->filter(function (mixed $v, mixed $k): bool {
            return is_numeric($v) && (float) $v >= 0;
        })->map(fn (mixed $v): int|float => is_int($v) ? $v : $v)->all();

        /** @var array<string, int|float> $filtered */
        $baseline = RuleCatalog::defaultThresholds();

        /** @var array<string, int|float> */
        $merged = array_merge($baseline, $filtered);

        return [$merged, $disabled];
    }

    /**
     * @param array<array-key, mixed> $rules Nested {structural: {...}, complexity: {...}, breathing: {...}}
     *
     * @return array{0: array<string, int|float>, 1: list<string>}
     */
    private static function flattenRules(array $rules): array
    {
        $scratch = new RuleFlattenScratch;

        foreach (RuleSections::sectionNames() as $section) {
            $sectionRules = $rules[$section] ?? [];

            if (! is_array($sectionRules)) {
                continue;
            }

            foreach ($sectionRules as $key => $value) {
                self::applySectionRuleEntry($key, $value, $scratch);
            }
        }

        return [$scratch->thresholds, array_values(array_unique($scratch->disabled))];
    }

    private static function applySectionRuleEntry(mixed $key, mixed $value, RuleFlattenScratch $scratch): void
    {
        if (! is_string($key) || ! array_key_exists($key, RuleCatalog::definitions())) {
            return;
        }

        if ($value === null) {
            $scratch->disabled[] = $key;

            return;
        }

        if (is_numeric($value)) {
            $scratch->thresholds[$key] = is_int($value) ? $value : (float) $value;
        }
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
}
