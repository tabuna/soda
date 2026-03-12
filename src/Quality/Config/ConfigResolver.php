<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

use Bunnivo\Soda\Quality\ConfigException;
use Bunnivo\Soda\Quality\QualityConfig;

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
     * @throws ConfigException
     */
    public function resolve(array $files, ?string $explicitPath = null): QualityConfig
    {
        $path = $this->locator->locate($files, $explicitPath);

        return $path !== null ? $this->loader->load($path) : $this->loader->loadDefault();
    }

    /**
     * @psalm-param list<non-empty-string> $files
     *
     * @throws ConfigException
     */
    public static function resolveConfig(array $files, ?string $explicitPath = null): QualityConfig
    {
        $resolver = new self(new ConfigLocator(), new ConfigLoader());

        return $resolver->resolve($files, $explicitPath);
    }
}
