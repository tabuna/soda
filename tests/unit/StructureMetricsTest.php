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

namespace Bunnivo\Soda;

use Bunnivo\Soda\Structure\Metrics;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Metrics::class)]
#[Small]
final class StructureMetricsTest extends TestCase
{
    private function createMetrics(array $overrides = []): Metrics
    {
        $data = array_merge([
            'namespaces'                   => 2,
            'interfaces'                   => 1,
            'traits'                       => 2,
            'abstractClasses'              => 1,
            'finalClasses'                 => 3,
            'nonFinalClasses'              => 2,
            'nonStaticMethods'             => 10,
            'staticMethods'                => 2,
            'publicMethods'                => 8,
            'protectedMethods'             => 2,
            'privateMethods'               => 2,
            'namedFunctions'               => 1,
            'anonymousFunctions'           => 2,
            'globalConstants'              => 0,
            'publicClassConstants'         => 5,
            'nonPublicClassConstants'      => 1,
            'globalVariableAccesses'       => 0,
            'superGlobalVariableAccesses'  => 0,
            'globalConstantAccesses'       => 0,
            'nonStaticAttributeAccesses'   => 10,
            'staticAttributeAccesses'      => 2,
            'nonStaticMethodCalls'         => 15,
            'staticMethodCalls'            => 5,
            'llocClasses'                  => 100,
            'llocFunctions'                => 20,
            'llocGlobal'                   => 5,
            'classLlocMin'                 => 5,
            'classLlocAvg'                 => 20,
            'classLlocMax'                 => 50,
            'methodLlocMin'                => 1,
            'methodLlocAvg'                => 8,
            'methodLlocMax'                => 25,
            'averageMethodsPerClass'       => 4,
            'minimumMethodsPerClass'       => 1,
            'maximumMethodsPerClass'       => 10,
            'averageFunctionLength'        => 10,
        ], $overrides);

        return new Metrics($data);
    }

    public function testClassesReturnsSumOfAbstractFinalAndNonFinal(): void
    {
        $m = $this->createMetrics();
        $this->assertSame(6, $m->classes());
    }

    public function testConcreteClassesReturnsFinalPlusNonFinal(): void
    {
        $m = $this->createMetrics();
        $this->assertSame(5, $m->concreteClasses());
    }

    public function testMethodsReturnsNonStaticPlusStatic(): void
    {
        $m = $this->createMetrics();
        $this->assertSame(12, $m->methods());
    }

    public function testFunctionsReturnsNamedPlusAnonymous(): void
    {
        $m = $this->createMetrics();
        $this->assertSame(3, $m->functions());
    }

    public function testConstantsReturnsGlobalPlusClassConstants(): void
    {
        $m = $this->createMetrics();
        $this->assertSame(6, $m->constants());
    }

    public function testClassConstantsReturnsPublicPlusNonPublic(): void
    {
        $m = $this->createMetrics();
        $this->assertSame(6, $m->classConstants());
    }

    public function testGlobalAccessesReturnsSum(): void
    {
        $m = $this->createMetrics([
            'globalVariableAccesses'      => 2,
            'superGlobalVariableAccesses' => 3,
            'globalConstantAccesses'      => 1,
        ]);
        $this->assertSame(6, $m->globalAccesses());
    }

    public function testAttributeAccessesReturnsSum(): void
    {
        $m = $this->createMetrics();
        $this->assertSame(12, $m->attributeAccesses());
    }

    public function testMethodCallsReturnsSum(): void
    {
        $m = $this->createMetrics();
        $this->assertSame(20, $m->methodCalls());
    }

    public function testLlocGetters(): void
    {
        $m = $this->createMetrics();
        $this->assertSame(100, $m->llocClasses());
        $this->assertSame(20, $m->llocFunctions());
        $this->assertSame(5, $m->llocGlobal());
    }

    public function testClassAndMethodLlocStats(): void
    {
        $m = $this->createMetrics();
        $this->assertSame(5, $m->classLlocMin());
        $this->assertSame(20, $m->classLlocAvg());
        $this->assertSame(50, $m->classLlocMax());
        $this->assertSame(1, $m->methodLlocMin());
        $this->assertSame(8, $m->methodLlocAvg());
        $this->assertSame(25, $m->methodLlocMax());
    }
}
