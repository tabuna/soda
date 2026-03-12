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
        'max_method_length'         => 20,
        'max_class_length'          => 500,
        'max_arguments'             => 3,
        'max_methods_per_class'     => 20,
        'max_file_loc'              => 400,
        'max_cyclomatic_complexity' => 10,
    ];

    /**
     * @psalm-var positive-int
     */
    public int $minScore;

    /**
     * @psalm-var array<string, positive-int>
     */
    public array $rules;

    /**
     * @psalm-param positive-int $minScore
     * @psalm-param array<string, positive-int> $rules
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

    private static function assertReadable(string $path): void
    {
        if (! is_readable($path)) {
            throw new ConfigException("Config file not readable: {$path}");
        }
    }

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

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @psalm-return positive-int
     */
    private static function parseMinScore(array $data): int
    {
        $minScore = Arr::get($data, 'quality.min_score', self::DEFAULT_MIN_SCORE);
        if (! is_int($minScore)) {
            $minScore = self::DEFAULT_MIN_SCORE;
        }

        if ($minScore < 1 || $minScore > 100) {
            throw new ConfigException('min_score must be between 1 and 100');
        }

        return $minScore;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @psalm-return array<string, positive-int>
     */
    private static function mergeRules(array $data): array
    {
        $rules = $data['rules'] ?? [];

        return array_merge(
            self::DEFAULT_RULES,
            collect($rules)->filter(fn ($v) => is_int($v) && $v > 0)->all(),
        );
    }

    public static function default(): self
    {
        return new self();
    }

    /**
     * @psalm-return int<0, max>
     */
    public function getRule(string $key): int
    {
        return $this->rules[$key] ?? 0;
    }
}
