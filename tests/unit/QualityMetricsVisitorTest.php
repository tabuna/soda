<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\Visitor\QualityMetricsVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class QualityMetricsVisitorTest extends TestCase
{
    private function parseAndCollect(string $code, int $fileLines = 50): array
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $visitor = new QualityMetricsVisitor($fileLines);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        return $visitor->result();
    }

    public function testCollectsPropertiesPerClass(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    private $a;
    protected $b, $c;
    public $d;
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertArrayHasKey('App\Foo', $result['classes']);
        $this->assertSame(4, $result['classes']['App\Foo']['properties']);
    }

    public function testCollectsPublicMethods(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function a() {}
    public function b() {}
    private function c() {}
    protected function d() {}
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(2, $result['classes']['App\Foo']['public_methods']);
    }

    public function testCollectsConstructorDependencies(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function __construct($a, $b, $c, $d) {}
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(4, $result['classes']['App\Foo']['dependencies']);
    }

    public function testCollectsTraitsPerClass(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
trait T1 {}
trait T2 {}
class Foo {
    use T1, T2;
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(2, $result['classes']['App\Foo']['traits']);
    }

    public function testCollectsInterfacesPerClass(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
interface I1 {}
interface I2 {}
interface I3 {}
class Foo implements I1, I2, I3 {}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(3, $result['classes']['App\Foo']['interfaces']);
    }

    public function testCollectsNamespaceDepth(): void
    {
        $code = <<<'PHP'
<?php
namespace App\Services\User\Internal;
class Foo {}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame('App\Services\User\Internal', $result['classes']['App\Services\User\Internal\Foo']['namespace']);
        $this->assertSame(4, $result['classes']['App\Services\User\Internal\Foo']['namespace_depth']);
    }

    public function testCollectsClassesPerFile(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class A {}
class B {}
trait C {}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(3, $result['classes_count']);
    }

    public function testCollectsMethodLocAndArgs(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar($a, $b, $c) {
        $x = 1;
        $y = 2;
        return $x + $y;
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertArrayHasKey('App\Foo::bar', $result['methods']);
        $this->assertSame(3, $result['methods']['App\Foo::bar']['args']);
        $this->assertGreaterThanOrEqual(5, $result['methods']['App\Foo::bar']['loc']);
    }

    public function testCollectsPrimaryClassTypes(): void
    {
        $code = <<<'PHP'
<?php
namespace App\Services;

interface Runs {}
interface Other {}
class BaseService {}

class UsesParent extends BaseService {}
class UsesInterface implements Runs, Other {}
class PlainClass {}
PHP;
        $result = $this->parseAndCollect($code);

        $this->assertSame('BaseService', $result['classTypes']['App\Services\UsesParent']);
        $this->assertSame('Runs', $result['classTypes']['App\Services\UsesInterface']);
        $this->assertSame('Plain', $result['classTypes']['App\Services\PlainClass']);
    }
}
