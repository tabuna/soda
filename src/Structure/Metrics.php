<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Structure;

/**
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

    public function namespaces(): int
    {
        return $this->data['namespaces'];
    }

    public function interfaces(): int
    {
        return $this->data['interfaces'];
    }

    public function traits(): int
    {
        return $this->data['traits'];
    }

    public function classes(): int
    {
        return $this->data['abstractClasses'] + $this->data['finalClasses'] + $this->data['nonFinalClasses'];
    }

    public function abstractClasses(): int
    {
        return $this->data['abstractClasses'];
    }

    public function concreteClasses(): int
    {
        return $this->data['finalClasses'] + $this->data['nonFinalClasses'];
    }

    public function finalClasses(): int
    {
        return $this->data['finalClasses'];
    }

    public function nonFinalClasses(): int
    {
        return $this->data['nonFinalClasses'];
    }

    public function methods(): int
    {
        return $this->data['nonStaticMethods'] + $this->data['staticMethods'];
    }

    public function nonStaticMethods(): int
    {
        return $this->data['nonStaticMethods'];
    }

    public function staticMethods(): int
    {
        return $this->data['staticMethods'];
    }

    public function publicMethods(): int
    {
        return $this->data['publicMethods'];
    }

    public function protectedMethods(): int
    {
        return $this->data['protectedMethods'];
    }

    public function privateMethods(): int
    {
        return $this->data['privateMethods'];
    }

    public function functions(): int
    {
        return $this->data['namedFunctions'] + $this->data['anonymousFunctions'];
    }

    public function namedFunctions(): int
    {
        return $this->data['namedFunctions'];
    }

    public function anonymousFunctions(): int
    {
        return $this->data['anonymousFunctions'];
    }

    public function constants(): int
    {
        return $this->data['globalConstants'] + $this->data['publicClassConstants'] + $this->data['nonPublicClassConstants'];
    }

    public function globalConstants(): int
    {
        return $this->data['globalConstants'];
    }

    public function classConstants(): int
    {
        return $this->data['publicClassConstants'] + $this->data['nonPublicClassConstants'];
    }

    public function publicClassConstants(): int
    {
        return $this->data['publicClassConstants'];
    }

    public function nonPublicClassConstants(): int
    {
        return $this->data['nonPublicClassConstants'];
    }

    public function globalAccesses(): int
    {
        return $this->data['globalVariableAccesses'] + $this->data['superGlobalVariableAccesses'] + $this->data['globalConstantAccesses'];
    }

    public function globalVariableAccesses(): int
    {
        return $this->data['globalVariableAccesses'];
    }

    public function superGlobalVariableAccesses(): int
    {
        return $this->data['superGlobalVariableAccesses'];
    }

    public function globalConstantAccesses(): int
    {
        return $this->data['globalConstantAccesses'];
    }

    public function attributeAccesses(): int
    {
        return $this->data['nonStaticAttributeAccesses'] + $this->data['staticAttributeAccesses'];
    }

    public function nonStaticAttributeAccesses(): int
    {
        return $this->data['nonStaticAttributeAccesses'];
    }

    public function staticAttributeAccesses(): int
    {
        return $this->data['staticAttributeAccesses'];
    }

    public function methodCalls(): int
    {
        return $this->data['nonStaticMethodCalls'] + $this->data['staticMethodCalls'];
    }

    public function nonStaticMethodCalls(): int
    {
        return $this->data['nonStaticMethodCalls'];
    }

    public function staticMethodCalls(): int
    {
        return $this->data['staticMethodCalls'];
    }

    public function llocClasses(): int
    {
        return $this->data['llocClasses'];
    }

    public function llocFunctions(): int
    {
        return $this->data['llocFunctions'];
    }

    public function llocGlobal(): int
    {
        return $this->data['llocGlobal'];
    }

    public function classLlocMin(): int
    {
        return $this->data['classLlocMin'];
    }

    public function classLlocAvg(): int
    {
        return $this->data['classLlocAvg'];
    }

    public function classLlocMax(): int
    {
        return $this->data['classLlocMax'];
    }

    public function methodLlocMin(): int
    {
        return $this->data['methodLlocMin'];
    }

    public function methodLlocAvg(): int
    {
        return $this->data['methodLlocAvg'];
    }

    public function methodLlocMax(): int
    {
        return $this->data['methodLlocMax'];
    }

    public function averageMethodsPerClass(): int
    {
        return $this->data['averageMethodsPerClass'];
    }

    public function minimumMethodsPerClass(): int
    {
        return $this->data['minimumMethodsPerClass'];
    }

    public function maximumMethodsPerClass(): int
    {
        return $this->data['maximumMethodsPerClass'];
    }

    public function averageFunctionLength(): int
    {
        return $this->data['averageFunctionLength'];
    }
}
