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
 * Resolves QualityConfig from project files or explicit path.
 */
final class ConfigResolver
{
    public function __construct(
        private readonly ConfigLocator $locator,
        private readonly ConfigLoader $loader,
    ) {}

    /**
     * @psalm-param list<non-empty-string> $files
     *
     * @throws QualityConfigException
     */
    public function resolve(array $files, ?string $explicitPath = null): QualityConfig
    {
        $path = $this->locator->locate($files, $explicitPath);

        return $path !== null ? $this->loader->load($path) : $this->loader->loadDefault();
    }

    /**
     * @psalm-param list<non-empty-string> $files
     *
     * @throws QualityConfigException
     */
    public static function resolveConfig(array $files, ?string $explicitPath = null): QualityConfig
    {
        $resolver = new self(new ConfigLocator(), new ConfigLoader());

        return $resolver->resolve($files, $explicitPath);
    }
}
