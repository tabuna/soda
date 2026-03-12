<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Bunnivo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda\Quality\Config;

use Bunnivo\Soda\Quality\QualityConfig;
use Bunnivo\Soda\Quality\QualityConfigException;

/**
 * Loads QualityConfig from file system.
 */
final class ConfigLoader
{
    /**
     * @psalm-param non-empty-string $path
     *
     * @throws QualityConfigException
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
