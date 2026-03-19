<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\ConfigException;
use Bunnivo\Soda\Quality\QualityAnalyser;
use Bunnivo\Soda\Quality\QualityAnalysisContract;
use Bunnivo\Soda\Quality\QualityResult;

/**
 * Fluent entry for running quality analysis (reads like a sentence).
 */
final class QualityAnalysisBuilder
{
    private bool $debug = false;

    private ?string $configPath = null;

    /**
     * @param list<non-empty-string> $paths
     */
    public function __construct(
        private readonly array $paths,
    ) {}

    public function debug(bool $on = true): self
    {
        $this->debug = $on;

        return $this;
    }

    /**
     * @param non-empty-string|null $path
     */
    public function config(?string $path): self
    {
        $this->configPath = $path;

        return $this;
    }

    /**
     * @throws ConfigException
     */
    public function run(?QualityAnalysisContract $engine = null): QualityResult
    {
        $engine ??= new QualityAnalyser;

        return $engine->analyse($this->paths, $this->debug, $this->configPath);
    }
}
