<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

/**
 * Base class for project quality rules: override {@see configure()} for IDE autocomplete on {@see SodaConfig}.
 *
 * Extend this class to add hooks, presets, or shared helpers while keeping a single entry file.
 */
abstract class SodaConfigurator
{
    abstract protected function configure(SodaConfig $config): void;

    final public function apply(SodaConfig $config): void
    {
        $this->configure($config);
    }

    /**
     * @param class-string<SodaConfigurator> $class
     *
     * @return \Closure(SodaConfig): void
     */
    public static function entry(string $class): \Closure
    {
        return static function (SodaConfig $config) use ($class): void {
            (new $class)->apply($config);
        };
    }
}
