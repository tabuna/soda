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
    private const DEFAULT_MIN_SCORE = 80;
    private const DEFAULT_RULES = [
        'max_method_length'                    => 20,
        'max_class_length'                     => 500,
        'max_arguments'                        => 3,
        'max_methods_per_class'                => 20,
        'max_file_loc'                         => 400,
        'max_cyclomatic_complexity'            => 10,
        'min_code_breathing_score'             => 0,
        'min_visual_breathing_index'           => 12,
        'min_identifier_readability_score'     => 75,
        'min_code_oxygen_level'                => 25,
        'max_weighted_cognitive_density'       => 30,
        'max_logical_complexity_factor'        => 35,
    ];

    /**
     * @psalm-var positive-int
     */
    public int $minScore;

    /**
     * @psalm-var array<string, int|float>
     */
    public array $rules;

    /**
     * @psalm-param positive-int $minScore
     * @psalm-param array<string, int|float> $rules
     */
    public function __construct(int $minScore = self::DEFAULT_MIN_SCORE, array $rules = self::DEFAULT_RULES)
    {
        $this->minScore = $minScore;
        $this->rules = $rules;
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
        $minScore = self::parseMinScore($data);
        $mergedRules = self::mergeRules($data);

        return new self($minScore, $mergedRules);
    }

    /**
     * @throws ConfigException
     */
    private static function assertReadable(string $path): void
    {
        if (! is_readable($path)) {
            throw new ConfigException("Config file not readable: {$path}");
        }
    }

    /**
     * @throws ConfigException
     */
    private static function readContent(string $path): string
    {
        $content = file_get_contents($path);
        if ($content === false) {
            throw new ConfigException("Cannot read config file: {$path}");
        }

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
        } catch (JsonException $e) {
            throw new ConfigException("Invalid JSON in config file {$path}: ".$e->getMessage());
        }

        if (! is_array($data)) {
            throw new ConfigException('Config must be a JSON object');
        }

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

        if ($minScore < 1 || $minScore > 100) {
            throw new ConfigException('min_score must be between 1 and 100');
        }

        return $minScore;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @psalm-return array<string, int|float>
     */
    private static function mergeRules(array $data): array
    {
        /** @var array<array-key, mixed> $rules */
        $rules = $data['rules'] ?? [];

        $filtered = collect($rules)->filter(function (mixed $v, mixed $k): bool {
            if (! is_numeric($v) || (float) $v < 0) {
                return false;
            }

            return (float) $v > 0 || (is_string($k) && str_starts_with($k, 'min_'));
        })->map(fn (mixed $v): int|float => is_int($v) ? $v : (float) $v)->all();

        if (isset($filtered['min_cbs']) && ! isset($filtered['min_code_breathing_score'])) {
            $filtered['min_code_breathing_score'] = $filtered['min_cbs'];
        }

        /** @var array<string, int|float> */
        return array_merge(self::DEFAULT_RULES, $filtered);
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
        $value = $this->rules[$key] ?? 0;

        if ($key === 'min_code_breathing_score' && $value <= 0 && isset($this->rules['min_cbs'])) {
            return $this->rules['min_cbs'];
        }

        return $value;
    }
}
