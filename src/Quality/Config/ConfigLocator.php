<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

use function dirname;

use Illuminate\Support\Collection;

use function is_readable;

/**
 * Locates config file path from project files.
 */
final class ConfigLocator
{
    private const array CONFIG_NAMES = ['soda.php', '.soda.php'];

    private const int MAX_DEPTH = 10;

    /**
     * @psalm-param list<non-empty-string> $files
     *
     * @return non-empty-string|null Path to config file, or null to use defaults
     */
    public function locate(array $files, ?string $explicitPath = null): ?string
    {
        if (($explicitPath ?? '') !== '') {
            return $explicitPath;
        }

        foreach ($this->uniqueParentDirs($files) as $dir) {
            $path = $this->firstConfigInAncestors($dir);

            if ($path !== null) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Optional PHP config in the **target project**: `config/soda.php` next to located `soda.php`, or found upward from scanned files (never vendor-only).
     *
     * @psalm-param list<non-empty-string> $files
     *
     * @return non-empty-string|null
     */
    public function locatePhpConfig(array $files, ?string $jsonConfigPath = null): ?string
    {
        $beside = ($jsonConfigPath ?? '') !== '' ? dirname((string) $jsonConfigPath).'/config/soda.php' : null;

        if ($beside !== null && is_readable($beside)) {
            return $beside;
        }

        foreach ($this->uniqueParentDirs($files) as $dir) {
            $found = $this->firstReadableInAncestors($dir, 'config/soda.php');

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * @psalm-param list<non-empty-string> $files
     *
     * @return Collection<int, string>
     */
    private function uniqueParentDirs(array $files)
    {
        return collect($files)
            ->map(fn (string $f) => dirname($f))
            ->unique()
            ->values();
    }

    /**
     * @return non-empty-string|null
     */
    private function firstConfigInAncestors(string $dir): ?string
    {
        return $this->walkAncestors($dir, function (string $current): ?string {
            foreach (self::CONFIG_NAMES as $name) {
                $path = $current.'/'.$name;

                if (is_readable($path) && $this->isExactFilename($path, $name)) {
                    return $path;
                }
            }

            return null;
        });
    }

    /**
     * @return non-empty-string|null
     */
    private function firstReadableInAncestors(string $dir, string $relativePath): ?string
    {
        return $this->walkAncestors($dir, function (string $current) use ($relativePath): ?string {
            $path = $current.'/'.$relativePath;

            if (! is_readable($path)) {
                return null;
            }

            $filename = basename($relativePath);

            return $this->isExactFilename($path, $filename) ? $path : null;
        });
    }

    /**
     * Guards against case-insensitive filesystems (e.g. macOS): verifies the real
     * file on disk has exactly the expected filename, not just a case-variant.
     * Uses scandir() because realpath() preserves input casing on macOS HFS+.
     */
    private function isExactFilename(string $path, string $filename): bool
    {
        $entries = scandir(dirname($path));

        return $entries !== false && in_array($filename, $entries, true);
    }

    /**
     * @param callable(string): ?non-empty-string $tryCurrent
     *
     * @return non-empty-string|null
     */
    private function walkAncestors(string $dir, callable $tryCurrent): ?string
    {
        $current = $dir;

        for ($i = 0; $i < self::MAX_DEPTH; $i++) {
            $hit = $tryCurrent($current);

            if ($hit !== null) {
                return $hit;
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
