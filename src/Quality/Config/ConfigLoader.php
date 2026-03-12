<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

use Bunnivo\Soda\Quality\ConfigException;
use Bunnivo\Soda\Quality\QualityConfig;

/**
 * Loads QualityConfig from file system.
 */
final class ConfigLoader
{
    /**
     * @psalm-param non-empty-string $path
     *
     * @throws ConfigException
     */
    public function load(string $path): QualityConfig
    {
        return QualityConfig::fromFile($path);
    }

    public function loadDefault(): QualityConfig
    {
        return QualityConfig::default();
    }
}
