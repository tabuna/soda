<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Structure;

/**
 * Mutable state for structure metrics collection. Encapsulates all counters.
 *
 * @internal
 */
final class MetricsState
{
    /** @var array<string, true> */
    public array $namespaces = [];

    /** @var list<non-empty-string> */
    public array $classStack = [];

    /** @var array<string, int|list<int>> */
    private array $data;

    public function __construct()
    {
        $this->data = [
            'interfaces'                   => 0,
            'traits'                       => 0,
            'abstractClasses'              => 0,
            'finalClasses'                 => 0,
            'nonFinalClasses'              => 0,
            'classLines'                   => [],
            'methodLines'                  => [],
            'methodsPerClass'              => [],
            'functionLines'                => [],
            'nonStaticMethods'             => 0,
            'staticMethods'                => 0,
            'publicMethods'                => 0,
            'protectedMethods'             => 0,
            'privateMethods'               => 0,
            'namedFunctions'               => 0,
            'anonymousFunctions'           => 0,
            'globalConstants'              => 0,
            'publicClassConstants'         => 0,
            'nonPublicClassConstants'      => 0,
            'globalVariableAccesses'       => 0,
            'superGlobalVariableAccesses'  => 0,
            'globalConstantAccesses'       => 0,
            'nonStaticAttributeAccesses'   => 0,
            'staticAttributeAccesses'      => 0,
            'nonStaticMethodCalls'         => 0,
            'staticMethodCalls'            => 0,
        ];
    }

    public function inc(string $key): void
    {
        $v = $this->data[$key];
        $this->data[$key] = (is_int($v) ? $v : 0) + 1;
    }

    public function add(string $key, int $value): void
    {
        $v = $this->data[$key];
        $this->data[$key] = (is_int($v) ? $v : 0) + $value;
    }

    public function push(string $key, int $value): void
    {
        /** @var list<int> $arr */
        $arr = $this->data[$key];
        $arr[] = $value;
        $this->data[$key] = $arr;
    }

    /**
     * @return array<string, mixed>
     */
    public function toResult(): array
    {
        return array_merge(
            ['namespaces' => $this->namespaces],
            $this->data,
        );
    }
}
