<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\Report\Violation;
use Illuminate\Support\Collection;

final readonly class ClassChecker
{
    public function __construct(
        private QualityConfig $config,
    ) {}

    /**
     * @psalm-param array<string, array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, efferent_coupling: int, traits: int, interfaces: int, namespace: string, namespace_depth: int}> $classes
     *
     * @return Collection<int, Violation>
     */
    public function check(string $file, array $classes): Collection
    {
        return collect($classes)
            ->flatMap(fn (array $data, string $class) => array_merge(
                $this->checkLoc($file, $class, $data),
                $this->checkMethods($file, $class, $data),
                $this->checkProperties($file, $class, $data),
                $this->checkPublic($file, $class, $data),
                $this->checkDependencies($file, $class, $data),
                $this->checkEfferentCoupling($file, $class, $data),
                $this->checkTraits($file, $class, $data),
            ))
            ->values();
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, efferent_coupling: int, traits: int, interfaces: int, namespace?: string, namespace_depth?: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkLoc(string $file, string $class, array $data): array
    {
        if (! $this->config->isRuleEnabled('max_class_length')) {
            return [];
        }

        $lines = $data['loc'];
        $max = $this->config->getRule('max_class_length');

        return $this
            ->whenExceeded('max_class_length')
            ->file($file)
            ->class($class)
            ->forValue($lines)
            ->limit($max)
            ->result();
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, efferent_coupling: int, traits: int, interfaces: int, namespace?: string, namespace_depth?: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkMethods(string $file, string $class, array $data): array
    {
        if (! $this->config->isRuleEnabled('max_methods_per_class')) {
            return [];
        }

        return $this
            ->whenExceeded('max_methods_per_class')
            ->file($file)
            ->class($class)
            ->forValue($data['methods'])
            ->limit($this->config->getRule('max_methods_per_class'))
            ->result();
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, efferent_coupling: int, traits: int, interfaces: int, namespace?: string, namespace_depth?: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkProperties(string $file, string $class, array $data): array
    {
        if (! $this->config->isRuleEnabled('max_properties_per_class')) {
            return [];
        }

        return $this
            ->whenExceeded('max_properties_per_class')
            ->file($file)
            ->class($class)
            ->forValue($data['properties'])
            ->limit($this->config->getRule('max_properties_per_class'))
            ->result();
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, efferent_coupling: int, traits: int, interfaces: int, namespace?: string, namespace_depth?: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkPublic(string $file, string $class, array $data): array
    {
        if (! $this->config->isRuleEnabled('max_public_methods')) {
            return [];
        }

        return $this
            ->whenExceeded('max_public_methods')
            ->file($file)
            ->class($class)
            ->forValue($data['public_methods'])
            ->limit($this->config->getRule('max_public_methods'))
            ->result();
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, efferent_coupling: int, traits: int, interfaces: int, namespace?: string, namespace_depth?: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkDependencies(string $file, string $class, array $data): array
    {
        if (! $this->config->isRuleEnabled('max_dependencies')) {
            return [];
        }

        return $this
            ->whenExceeded('max_dependencies')
            ->file($file)
            ->class($class)
            ->forValue($data['dependencies'])
            ->limit($this->config->getRule('max_dependencies'))
            ->result();
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, efferent_coupling: int, traits: int, interfaces: int, namespace?: string, namespace_depth?: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkEfferentCoupling(string $file, string $class, array $data): array
    {
        if (! $this->config->isRuleEnabled('max_efferent_coupling')) {
            return [];
        }

        $max = (int) $this->config->getRule('max_efferent_coupling');
        if ($max <= 0) {
            return [];
        }

        return $this
            ->whenExceeded('max_efferent_coupling')
            ->file($file)
            ->class($class)
            ->forValue($data['efferent_coupling'])
            ->limit($max)
            ->result();
    }

    private function whenExceeded(string $rule): RuleChecker
    {
        return RuleChecker::whenExceeded($rule);
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, efferent_coupling: int, traits: int, interfaces: int, namespace?: string, namespace_depth?: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkTraits(string $file, string $class, array $data): array
    {
        $traits = [];

        if ($this->config->isRuleEnabled('max_traits_per_class')) {
            $traits = $this
                ->whenExceeded('max_traits_per_class')
                ->file($file)
                ->class($class)
                ->forValue($data['traits'])
                ->limit($this->config->getRule('max_traits_per_class'))
                ->result();
        }

        $interfaces = [];

        if ($this->config->isRuleEnabled('max_interfaces_per_class')) {
            $interfaces = $this
                ->whenExceeded('max_interfaces_per_class')
                ->file($file)
                ->class($class)
                ->forValue($data['interfaces'])
                ->limit($this->config->getRule('max_interfaces_per_class'))
                ->result();
        }

        return array_merge($traits, $interfaces);
    }
}
