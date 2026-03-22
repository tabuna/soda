<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\Visitor\EfferentCouplingVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(EfferentCouplingVisitor::class)]
#[Small]
final class EfferentCouplingVisitorTest extends TestCase
{
    /**
     * @psalm-return array<string, int>
     */
    private function parseAndCollect(string $code): array
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $visitor = new EfferentCouplingVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        return $visitor->result();
    }

    public function testCountsDistinctExternalTypes(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class A {}
class B {}
class C {}
class D {}
class E { public static function m(): void {} }
interface I {}
trait T {}
class Foo extends A implements I {
    use T;
    private B $injected;
    public function x(B $b): C {
        return new D();
    }
    public static function y(): void {
        E::m();
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(7, $result['App\Foo']);
    }

    public function testSelfReturnNotCountedAsExternal(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function chain(): self {
        return $this;
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(0, $result['App\Foo']);
    }

    public function testTraitListsUsedTraits(): void
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
        $this->assertSame(2, $result['App\Foo']);
    }
}
