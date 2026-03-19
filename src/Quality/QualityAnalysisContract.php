<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

/**
 * Entry point for the full quality pipeline (metrics + rule evaluation).
 */
interface QualityAnalysisContract
{
    /**
     * @psalm-param list<non-empty-string> $files
     * @psalm-param non-empty-string|null $configPath
     *
     * @throws ConfigException
     */
    public function analyse(array $files, bool $debug, ?string $configPath = null): QualityResult;
}
