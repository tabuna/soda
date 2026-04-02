<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\Visitor\BooleanConditionsVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class BooleanConditionsVisitorTest extends TestCase
{
    private function parseAndCollect(string $code): array
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $visitor = new BooleanConditionsVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        return $visitor->result();
    }

    public function testSimpleCondition(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar($a) {
        if ($a) {
            return 1;
        }
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertArrayHasKey('App\Foo::bar', $result);
        $this->assertCount(1, $result['App\Foo::bar']);
        $this->assertSame(1, $result['App\Foo::bar'][0]['count']);
    }

    public function testMultipleConditions(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar($a, $b, $c, $d, $e) {
        if ($a && $b && ($c || $d) && !$e) {
            doSomething();
        }
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertArrayHasKey('App\Foo::bar', $result);
        $this->assertCount(1, $result['App\Foo::bar']);
        $this->assertSame(5, $result['App\Foo::bar'][0]['count']);
    }

    public function testTernaryCondition(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar($a, $b) {
        return $a && $b ? 1 : 0;
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertArrayHasKey('App\Foo::bar', $result);
        $this->assertCount(1, $result['App\Foo::bar']);
        $this->assertSame(2, $result['App\Foo::bar'][0]['count']);
    }

    public function testWhileCondition(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar($a, $b, $c) {
        while ($a && $b && $c) {
            break;
        }
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertArrayHasKey('App\Foo::bar', $result);
        $this->assertSame(3, $result['App\Foo::bar'][0]['count']);
    }

    public function testClosureNotCounted(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar($items) {
        return array_filter($items, function ($x) {
            return $x && $y && $z;
        });
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertArrayHasKey('App\Foo::bar', $result);
        $this->assertCount(0, $result['App\Foo::bar']);
    }

    public function testTopLevelFunction(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
function process($a, $b) {
    if ($a || $b) {
        return true;
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertArrayHasKey('App\process', $result);
        $this->assertSame(2, $result['App\process'][0]['count']);
    }
}
