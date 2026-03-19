<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\Config\ConfigLocator;
use Bunnivo\Soda\Quality\Config\ConfigResolver;
use Bunnivo\Soda\Quality\Config\PhpSodaConfig;

/**
 * @internal
 */
final class QualityAnalyserConfigurationSession
{
    /**
     * @psalm-param list<non-empty-string> $files
     *
     * @throws ConfigException
     */
    public static function engineForFiles(array $files, ?string $configPath): QualityEngine
    {
        $locator = new ConfigLocator;

        $jsonPath = $locator->locate($files, $configPath);

        $config = ConfigResolver::resolveConfig($files, $configPath);

        $phpConfigPath = $locator->locatePhpConfig($files, $jsonPath);

        return QualityEngine::create($config, PhpSodaConfig::checkersFromPath($phpConfigPath));
    }
}
