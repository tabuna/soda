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
        throw_unless(
            str_ends_with($path, '.php'),
            ConfigException::class,
            sprintf('Config must be a .php file; JSON is no longer supported (%s).', $path)
        );

        return QualityConfig::fromPhpConfiguratorFile($path);
    }

    public function loadDefault(): QualityConfig
    {
        return QualityConfig::default();
    }
}
