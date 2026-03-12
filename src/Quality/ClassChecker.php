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

namespace Bunnivo\Soda\Quality;

use function array_merge;

final class ClassChecker
{
    public function __construct(
        private readonly QualityConfig $config,
    ) {}

    /**
     * @psalm-param array<string, array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, traits: int, interfaces: int, namespace: string, namespace_depth: int}> $classes
     *
     * @psalm-return list<Violation>
     */
    public function check(string $file, array $classes): array
    {
        $violations = [];
        foreach ($classes as $className => $data) {
            $violations = array_merge(
                $violations,
                $this->checkClassLength($file, $className, $data),
                $this->checkMethodsCount($file, $className, $data),
                $this->checkPropertiesCount($file, $className, $data),
                $this->checkPublicMethods($file, $className, $data),
                $this->checkDependencies($file, $className, $data),
                $this->checkTraitsAndInterfaces($file, $className, $data),
            );
        }

        return $violations;
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, traits: int, interfaces: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkClassLength(string $file, string $className, array $data): array
    {
        $max = $this->config->getRule('max_class_length');
        if ($max <= 0 || $data['loc'] <= $max) {
            return [];
        }

        return [ViolationBuilder::of('max_class_length', $file, new Limits($data['loc'], $max))->atClass($className)->build()];
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, traits: int, interfaces: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkMethodsCount(string $file, string $className, array $data): array
    {
        $max = $this->config->getRule('max_methods_per_class');
        if ($max <= 0 || $data['methods'] <= $max) {
            return [];
        }

        return [ViolationBuilder::of('max_methods_per_class', $file, new Limits($data['methods'], $max))->atClass($className)->build()];
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, traits: int, interfaces: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkPropertiesCount(string $file, string $className, array $data): array
    {
        $max = $this->config->getRule('max_properties_per_class');
        if ($max <= 0 || $data['properties'] <= $max) {
            return [];
        }

        return [ViolationBuilder::of('max_properties_per_class', $file, new Limits($data['properties'], $max))->atClass($className)->build()];
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, traits: int, interfaces: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkPublicMethods(string $file, string $className, array $data): array
    {
        $max = $this->config->getRule('max_public_methods');
        if ($max <= 0 || $data['public_methods'] <= $max) {
            return [];
        }

        return [ViolationBuilder::of('max_public_methods', $file, new Limits($data['public_methods'], $max))->atClass($className)->build()];
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, traits: int, interfaces: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkDependencies(string $file, string $className, array $data): array
    {
        $max = $this->config->getRule('max_dependencies');
        if ($max <= 0 || $data['dependencies'] <= $max) {
            return [];
        }

        return [ViolationBuilder::of('max_dependencies', $file, new Limits($data['dependencies'], $max))->atClass($className)->build()];
    }

    /**
     * @psalm-param array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, traits: int, interfaces: int} $data
     *
     * @psalm-return list<Violation>
     */
    private function checkTraitsAndInterfaces(string $file, string $className, array $data): array
    {
        $violations = [];
        $maxTraits = $this->config->getRule('max_traits_per_class');
        if ($maxTraits > 0 && $data['traits'] > $maxTraits) {
            $violations[] = ViolationBuilder::of('max_traits_per_class', $file, new Limits($data['traits'], $maxTraits))->atClass($className)->build();
        }
        $maxInterfaces = $this->config->getRule('max_interfaces_per_class');
        if ($maxInterfaces > 0 && $data['interfaces'] > $maxInterfaces) {
            $violations[] = ViolationBuilder::of('max_interfaces_per_class', $file, new Limits($data['interfaces'], $maxInterfaces))->atClass($className)->build();
        }

        return $violations;
    }
}
