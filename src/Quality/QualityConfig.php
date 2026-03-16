<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use function file_get_contents;

use Illuminate\Support\Arr;

use function is_array;
use function json_decode;

use JsonException;

final readonly class QualityConfig
{
    private const int DEFAULT_MIN_SCORE = 80;

    private const array DEFAULT_RULES = [
        'max_method_length'                     => 20,
        'max_class_length'                      => 500,
        'max_arguments'                         => 3,
        'max_control_nesting'                   => 3,
        'max_methods_per_class'                 => 20,
        'max_file_loc'                          => 400,
        'max_cyclomatic_complexity'             => 10,
        'max_properties_per_class'              => 20,
        'max_public_methods'                    => 20,
        'max_dependencies'                      => 10,
        'max_classes_per_file'                  => 1,
        'max_namespace_depth'                   => 4,
        'max_classes_per_namespace'             => 40,
        'max_traits_per_class'                  => 5,
        'max_interfaces_per_class'              => 5,
        'max_classes_per_project'               => 2000,
        'min_code_breathing_score'              => 0,
        'min_visual_breathing_index'            => 12,
        'min_identifier_readability_score'      => 75,
        'min_code_oxygen_level'                 => 25,
        'max_weighted_cognitive_density'        => 30,
        'max_logical_complexity_factor'         => 35,
        'max_return_statements'                 => 4,
        'max_boolean_conditions'                => 3,
    ];

    /**
     * @psalm-param positive-int $minScore
     * @psalm-param array<string, int|float> $rules
     */
    public function __construct(
        /**
         * @psalm-var positive-int
         */
        public int $minScore = self::DEFAULT_MIN_SCORE,
        /**
         * @psalm-var array<string, int|float>
         */
        public array $rules = self::DEFAULT_RULES
    ) {}

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
        $minScore = self::parseMinScore($data);
        $mergedRules = self::mergeRules($data);

        return new self($minScore, $mergedRules);
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
            throw new ConfigException(sprintf('Invalid JSON in config file %s: ', $path).$jsonException->getMessage(), $jsonException->getCode(), $jsonException);
        }

        throw_unless(is_array($data), ConfigException::class, 'Config must be a JSON object');

        /** @var array<string, mixed> $data */
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws ConfigException
     *
     * @psalm-return positive-int
     */
    private static function parseMinScore(array $data): int
    {
        $raw = Arr::get($data, 'quality.min_score', self::DEFAULT_MIN_SCORE);
        $minScore = is_int($raw) ? $raw : self::DEFAULT_MIN_SCORE;

        throw_if($minScore < 1 || $minScore > 100, ConfigException::class, 'min_score must be between 1 and 100');

        return $minScore;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @psalm-return array<string, int|float>
     */
    private static function mergeRules(array $data): array
    {
        /** @var array<array-key, mixed> $raw */
        $raw = $data['rules'] ?? [];

        $flattened = self::flattenRules($raw);
        $filtered = collect($flattened)->filter(function (mixed $v, mixed $k): bool {
            return is_numeric($v) && (float) $v >= 0;
        })->map(fn (mixed $v): int|float => is_int($v) ? $v : $v)->all();

        /** @var array<string, int|float> */
        return array_merge(self::DEFAULT_RULES, $filtered);
    }

    /**
     * @param array<array-key, mixed> $rules Nested {structural: {...}, complexity: {...}, breathing: {...}}
     *
     * @return array<string, int|float>
     */
    private static function flattenRules(array $rules): array
    {
        $result = [];

        foreach (RuleSections::sectionNames() as $section) {
            $sectionRules = $rules[$section] ?? [];

            if (! is_array($sectionRules)) {
                continue;
            }

            foreach ($sectionRules as $key => $value) {
                if (is_string($key) && is_numeric($value)) {
                    $result[$key] = is_int($value) ? $value : (float) $value;
                }
            }
        }

        return $result;
    }

    public static function default(): self
    {
        return new self();
    }

    /**
     * @psalm-return int|float
     */
    public function getRule(string $key): int|float
    {
        return $this->rules[$key] ?? 0;
    }
}
