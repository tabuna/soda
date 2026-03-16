<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\ControlNestingVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ControlNestingVisitor::class)]
#[Small]
final class ControlNestingVisitorTest extends TestCase
{
    private function parseAndCollect(string $code): array
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $visitor = new ControlNestingVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        return $visitor->result();
    }

    public function testNoNestingReturnsZero(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar() {
        return 1;
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertArrayHasKey('App\Foo::bar', $result);
        $this->assertSame(0, $result['App\Foo::bar']['depth']);
    }

    public function testSingleIfDepthOne(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar() {
        if (true) {
            return 1;
        }
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(1, $result['App\Foo::bar']['depth']);
    }

    public function testNestedForeachIfDepthTwo(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar($users) {
        foreach ($users as $user) {
            if ($user->active) {
                process($user);
            }
        }
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(2, $result['App\Foo::bar']['depth']);
    }

    public function testDeepNestingExceedsLimit(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar($users) {
        foreach ($users as $user) {
            if ($user->active) {
                foreach ($user->orders as $order) {
                    if ($order->paid) {
                        process($order);
                    }
                }
            }
        }
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(4, $result['App\Foo::bar']['depth']);
    }

    public function testClosureResetsContext(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar($users) {
        return array_map(function ($u) {
            foreach ($u->items as $i) {
                if ($i->valid) {
                    return $i;
                }
            }
        }, $users);
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(0, $result['App\Foo::bar']['depth']);
    }

    public function testTryCatchAddsDepth(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar() {
        try {
            if (true) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            return 1;
        }
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertGreaterThanOrEqual(2, $result['App\Foo::bar']['depth']);
    }

    public function testTopLevelFunction(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
function process($x) {
    if ($x) {
        return 1;
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertArrayHasKey('App\process', $result);
        $this->assertSame(1, $result['App\process']['depth']);
    }
}
