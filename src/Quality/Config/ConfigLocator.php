<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

use function dirname;
use function is_readable;

/**
 * Locates config file path from project files.
 */
final class ConfigLocator
{
    private const CONFIG_NAMES = ['soda.json', '.soda.json', 'code-quality.json', '.code-quality.json'];
    private const MAX_DEPTH = 10;

    /**
     * @psalm-param list<non-empty-string> $files
     *
     * @return non-empty-string|null Path to config file, or null to use defaults
     */
    public function locate(array $files, ?string $explicitPath = null): ?string
    {
        if ($explicitPath !== null && $explicitPath !== '') {
            return $explicitPath;
        }

        $dirs = collect($files)
            ->map(fn (string $f) => dirname($f))
            ->unique()
            ->values();

        foreach ($dirs as $dir) {
            $path = $this->searchUpward($dir);
            if ($path !== null) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return non-empty-string|null
     */
    private function searchUpward(string $dir): ?string
    {
        $current = $dir;

        for ($i = 0; $i < self::MAX_DEPTH; $i++) {
            foreach (self::CONFIG_NAMES as $name) {
                $path = $current.'/'.$name;
                if (is_readable($path)) {
                    return $path;
                }
            }
            $parent = dirname($current);
            if ($parent === $current) {
                break;
            }
            $current = $parent;
        }

        return null;
    }
}
