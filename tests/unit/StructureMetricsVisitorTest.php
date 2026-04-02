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

use Bunnivo\Soda\Structure\MetricsVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class StructureMetricsVisitorTest extends TestCase
{
    private function parseAndCollect(string $code): array
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $visitor = new MetricsVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        return $visitor->result();
    }

    public function testCollectsNamespaces(): void
    {
        $code = <<<'PHP'
<?php
namespace App\Services;
class Foo {}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertArrayHasKey('App\Services', $result['namespaces']);
    }

    public function testCollectsInterfaces(): void
    {
        $code = <<<'PHP'
<?php
interface I1 {}
interface I2 {}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(2, $result['interfaces']);
    }

    public function testCollectsTraits(): void
    {
        $code = <<<'PHP'
<?php
trait T1 {}
trait T2 {}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(2, $result['traits']);
    }

    public function testCollectsAbstractAndFinalClasses(): void
    {
        $code = <<<'PHP'
<?php
abstract class A {}
final class B {}
class C {}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(1, $result['abstractClasses']);
        $this->assertSame(1, $result['finalClasses']);
        $this->assertSame(1, $result['nonFinalClasses']);
    }

    public function testCollectsMethodVisibilityAndStatic(): void
    {
        $code = <<<'PHP'
<?php
class Foo {
    public function a() {}
    protected function b() {}
    private function c() {}
    public static function d() {}
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(2, $result['publicMethods']);
        $this->assertSame(1, $result['protectedMethods']);
        $this->assertSame(1, $result['privateMethods']);
        $this->assertSame(3, $result['nonStaticMethods']);
        $this->assertSame(1, $result['staticMethods']);
    }

    public function testSkipsInterfaceMethods(): void
    {
        $code = <<<'PHP'
<?php
interface I {
    public function foo();
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(0, $result['publicMethods']);
    }

    public function testCollectsNamedFunctions(): void
    {
        $code = <<<'PHP'
<?php
function foo() {}
function bar() {}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(2, $result['namedFunctions']);
        $this->assertSame(0, $result['anonymousFunctions']);
    }

    public function testCollectsClassConstants(): void
    {
        $code = <<<'PHP'
<?php
class Foo {
    public const A = 1;
    private const B = 2;
    protected const C = 3;
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(1, $result['publicClassConstants']);
        $this->assertSame(2, $result['nonPublicClassConstants']);
    }

    public function testCollectsGlobalConstants(): void
    {
        $code = <<<'PHP'
<?php
const A = 1;
const B = 2;
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(2, $result['globalConstants']);
    }

    public function testCollectsMethodCalls(): void
    {
        $code = <<<'PHP'
<?php
class Foo {
    public function bar() {
        $this->baz();
        self::qux();
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(1, $result['nonStaticMethodCalls']);
        $this->assertSame(1, $result['staticMethodCalls']);
    }

    public function testCollectsAttributeAccesses(): void
    {
        $code = <<<'PHP'
<?php
class Foo {
    public static $a;
    public $b;
    public function bar() {
        $this->b = 1;
        self::$a = 2;
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(1, $result['nonStaticAttributeAccesses']);
        $this->assertSame(1, $result['staticAttributeAccesses']);
    }

    public function testCollectsSuperGlobals(): void
    {
        $code = <<<'PHP'
<?php
function foo() {
    $x = $_GET;
    $y = $_POST;
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(2, $result['superGlobalVariableAccesses']);
    }

    public function testCollectsGlobalVariableAccesses(): void
    {
        $code = <<<'PHP'
<?php
function foo() {
    global $x;
}
function bar() {
    $a = $GLOBALS;
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(2, $result['globalVariableAccesses']);
    }

    public function testSkipsAnonymousClass(): void
    {
        $code = <<<'PHP'
<?php
class Foo {
    public function bar() {
        return new class extends \stdClass {};
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(1, $result['abstractClasses'] + $result['finalClasses'] + $result['nonFinalClasses']);
    }

    public function testMergeAggregatesResults(): void
    {
        $r1 = [
            'namespaces'                   => ['App' => true],
            'interfaces'                   => 1,
            'traits'                       => 0,
            'abstractClasses'              => 0,
            'finalClasses'                 => 1,
            'nonFinalClasses'              => 0,
            'classLines'                   => [10],
            'methodLines'                  => [5],
            'methodsPerClass'              => [1],
            'functionLines'                => [],
            'nonStaticMethods'             => 1,
            'staticMethods'                => 0,
            'publicMethods'                => 1,
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
        $r2 = [
            'namespaces'                   => ['App\Other' => true],
            'interfaces'                   => 0,
            'traits'                       => 1,
            'abstractClasses'              => 1,
            'finalClasses'                 => 0,
            'nonFinalClasses'              => 0,
            'classLines'                   => [20],
            'methodLines'                  => [3, 4],
            'methodsPerClass'              => [1, 1],
            'functionLines'                => [7],
            'nonStaticMethods'             => 2,
            'staticMethods'                => 0,
            'publicMethods'                => 2,
            'protectedMethods'             => 0,
            'privateMethods'               => 0,
            'namedFunctions'               => 1,
            'anonymousFunctions'           => 0,
            'globalConstants'              => 0,
            'publicClassConstants'         => 1,
            'nonPublicClassConstants'      => 0,
            'globalVariableAccesses'       => 0,
            'superGlobalVariableAccesses'  => 0,
            'globalConstantAccesses'       => 0,
            'nonStaticAttributeAccesses'   => 1,
            'staticAttributeAccesses'      => 0,
            'nonStaticMethodCalls'         => 1,
            'staticMethodCalls'            => 0,
        ];
        $merged = MetricsVisitor::merge([$r1, $r2]);
        $this->assertSame(2, $merged['namespaces']);
        $this->assertSame(1, $merged['interfaces']);
        $this->assertSame(1, $merged['traits']);
        $this->assertSame(1, $merged['abstractClasses']);
        $this->assertSame(1, $merged['finalClasses']);
        $this->assertSame(0, $merged['nonFinalClasses']);
        $this->assertSame([10, 20], $merged['classLines']);
        $this->assertSame([5, 3, 4], $merged['methodLines']);
        $this->assertSame(3, $merged['nonStaticMethods']);
        $this->assertSame(1, $merged['namedFunctions']);
        $this->assertSame(1, $merged['publicClassConstants']);
        $this->assertSame(1, $merged['nonStaticAttributeAccesses']);
        $this->assertSame(1, $merged['nonStaticMethodCalls']);
    }

    public function testComputeStatsCalculatesAverages(): void
    {
        $stats = [
            'classLines'      => [10, 20, 30],
            'methodLines'     => [2, 4, 6],
            'methodsPerClass' => [1, 2, 3],
            'functionLines'   => [5, 15],
        ];
        $result = MetricsVisitor::computeStats($stats, 50);
        $this->assertSame(12, $result['llocClasses']);
        $this->assertSame(20, $result['llocFunctions']);
        $this->assertSame(18, $result['llocGlobal']);
        $this->assertSame(10, $result['classLlocMin']);
        $this->assertSame(20, $result['classLlocAvg']);
        $this->assertSame(30, $result['classLlocMax']);
        $this->assertSame(2, $result['methodLlocMin']);
        $this->assertSame(4, $result['methodLlocAvg']);
        $this->assertSame(6, $result['methodLlocMax']);
        $this->assertSame(2, $result['averageMethodsPerClass']);
        $this->assertSame(1, $result['minimumMethodsPerClass']);
        $this->assertSame(3, $result['maximumMethodsPerClass']);
        $this->assertSame(10, $result['averageFunctionLength']);
    }

    public function testComputeStatsWithEmptyData(): void
    {
        $stats = [
            'classLines'      => [],
            'methodLines'     => [],
            'methodsPerClass' => [],
            'functionLines'   => [],
        ];
        $result = MetricsVisitor::computeStats($stats, 0);

        $this->assertSame(0, $result['llocClasses']);
        $this->assertSame(0, $result['llocFunctions']);
        $this->assertSame(0, $result['llocGlobal']);
        $this->assertSame(0, $result['classLlocMin']);
        $this->assertSame(0, $result['classLlocAvg']);
        $this->assertSame(0, $result['classLlocMax']);
        $this->assertSame(0, $result['methodLlocMin']);
        $this->assertSame(0, $result['methodLlocAvg']);
        $this->assertSame(0, $result['methodLlocMax']);
        $this->assertSame(0, $result['averageMethodsPerClass']);
        $this->assertSame(0, $result['minimumMethodsPerClass']);
        $this->assertSame(0, $result['maximumMethodsPerClass']);
        $this->assertSame(0, $result['averageFunctionLength']);
    }

    public function testComputeStatsScalesWhenExceedingLloc(): void
    {
        $stats = [
            'classLines'      => [100],
            'methodLines'     => [80],
            'methodsPerClass' => [1],
            'functionLines'   => [50],
        ];
        $result = MetricsVisitor::computeStats($stats, 50);

        $this->assertLessThanOrEqual(50, $result['llocClasses'] + $result['llocFunctions'] + $result['llocGlobal']);
        $this->assertGreaterThanOrEqual(0, $result['llocGlobal']);
    }

    public function testMergeWithEmptyResults(): void
    {
        $merged = MetricsVisitor::merge([]);

        $this->assertSame(0, $merged['namespaces']);
        $this->assertSame(0, $merged['interfaces']);
        $this->assertSame(0, $merged['traits']);
        $this->assertSame([], $merged['classLines']);
        $this->assertSame([], $merged['methodLines']);
    }
}
