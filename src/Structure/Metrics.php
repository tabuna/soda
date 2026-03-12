<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Structure;

/**
 * Structure metrics with single accessor for soda compliance.
 *
 * @param array{
 *   namespaces: int,
 *   interfaces: int,
 *   traits: int,
 *   abstractClasses: int,
 *   finalClasses: int,
 *   nonFinalClasses: int,
 *   nonStaticMethods: int,
 *   staticMethods: int,
 *   publicMethods: int,
 *   protectedMethods: int,
 *   privateMethods: int,
 *   namedFunctions: int,
 *   anonymousFunctions: int,
 *   globalConstants: int,
 *   publicClassConstants: int,
 *   nonPublicClassConstants: int,
 *   globalVariableAccesses: int,
 *   superGlobalVariableAccesses: int,
 *   globalConstantAccesses: int,
 *   nonStaticAttributeAccesses: int,
 *   staticAttributeAccesses: int,
 *   nonStaticMethodCalls: int,
 *   staticMethodCalls: int,
 *   llocClasses: int,
 *   llocFunctions: int,
 *   llocGlobal: int,
 *   classLlocMin: int,
 *   classLlocAvg: int,
 *   classLlocMax: int,
 *   methodLlocMin: int,
 *   methodLlocAvg: int,
 *   methodLlocMax: int,
 *   averageMethodsPerClass: int,
 *   minimumMethodsPerClass: int,
 *   maximumMethodsPerClass: int,
 *   averageFunctionLength: int,
 * } $data
 */
final readonly class Metrics
{
    public function __construct(private array $data) {}

    public function get(string $key): int
    {
        $arr = $this->toArray();

        return $arr[$key] ?? 0;
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        $d = $this->data;

        return array_merge(
            $this->structureCounts($d),
            $this->methodAndFunctionCounts($d),
            $this->constantAndAccessCounts($d),
            $this->llocStats($d),
        );
    }

    /**
     * @param array<string, int> $d
     *
     * @return array<string, int>
     */
    private function structureCounts(array $d): array
    {

        return [
            'namespaces'      => $d['namespaces'],
            'interfaces'      => $d['interfaces'],
            'traits'          => $d['traits'],
            'classes'         => $d['abstractClasses'] + $d['finalClasses'] + $d['nonFinalClasses'],
            'abstractClasses' => $d['abstractClasses'],
            'concreteClasses' => $d['finalClasses'] + $d['nonFinalClasses'],
            'finalClasses'    => $d['finalClasses'],
            'nonFinalClasses' => $d['nonFinalClasses'],
        ];
    }

    /**
     * @param array<string, int> $d
     *
     * @return array<string, int>
     */
    private function methodAndFunctionCounts(array $d): array
    {

        return [
            'methods'             => $d['nonStaticMethods'] + $d['staticMethods'],
            'nonStaticMethods'    => $d['nonStaticMethods'],
            'staticMethods'       => $d['staticMethods'],
            'publicMethods'       => $d['publicMethods'],
            'protectedMethods'    => $d['protectedMethods'],
            'privateMethods'      => $d['privateMethods'],
            'functions'           => $d['namedFunctions'] + $d['anonymousFunctions'],
            'namedFunctions'      => $d['namedFunctions'],
            'anonymousFunctions'  => $d['anonymousFunctions'],
        ];
    }

    /**
     * @param array<string, int> $d
     *
     * @return array<string, int>
     */
    private function constantAndAccessCounts(array $d): array
    {

        return [
            'constants'                   => $d['globalConstants'] + $d['publicClassConstants'] + $d['nonPublicClassConstants'],
            'globalConstants'             => $d['globalConstants'],
            'classConstants'              => $d['publicClassConstants'] + $d['nonPublicClassConstants'],
            'publicClassConstants'        => $d['publicClassConstants'],
            'nonPublicClassConstants'     => $d['nonPublicClassConstants'],
            'globalAccesses'              => $d['globalVariableAccesses'] + $d['superGlobalVariableAccesses'] + $d['globalConstantAccesses'],
            'globalVariableAccesses'      => $d['globalVariableAccesses'],
            'superGlobalVariableAccesses' => $d['superGlobalVariableAccesses'],
            'globalConstantAccesses'      => $d['globalConstantAccesses'],
            'attributeAccesses'           => $d['nonStaticAttributeAccesses'] + $d['staticAttributeAccesses'],
            'nonStaticAttributeAccesses'  => $d['nonStaticAttributeAccesses'],
            'staticAttributeAccesses'     => $d['staticAttributeAccesses'],
            'methodCalls'                 => $d['nonStaticMethodCalls'] + $d['staticMethodCalls'],
            'nonStaticMethodCalls'        => $d['nonStaticMethodCalls'],
            'staticMethodCalls'           => $d['staticMethodCalls'],
        ];
    }

    /**
     * @param array<string, int> $d
     *
     * @return array<string, int>
     */
    private function llocStats(array $d): array
    {

        return [
            'llocClasses'               => $d['llocClasses'],
            'llocFunctions'             => $d['llocFunctions'],
            'llocGlobal'                => $d['llocGlobal'],
            'classLlocMin'              => $d['classLlocMin'],
            'classLlocAvg'              => $d['classLlocAvg'],
            'classLlocMax'              => $d['classLlocMax'],
            'methodLlocMin'             => $d['methodLlocMin'],
            'methodLlocAvg'             => $d['methodLlocAvg'],
            'methodLlocMax'             => $d['methodLlocMax'],
            'averageMethodsPerClass'    => $d['averageMethodsPerClass'],
            'minimumMethodsPerClass'    => $d['minimumMethodsPerClass'],
            'maximumMethodsPerClass'    => $d['maximumMethodsPerClass'],
            'averageFunctionLength'     => $d['averageFunctionLength'],
        ];
    }
}
