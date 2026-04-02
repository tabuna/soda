<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\Visitor\TryCatchCountVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class TryCatchCountVisitorTest extends TestCase
{
    private function parseAndCollect(string $code): array
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $visitor = new TryCatchCountVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        return $visitor->result();
    }

    public function testThreeTryCatchInMethod(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    function run() {
        try {} catch (\Exception $e) {}
        try {} catch (\Exception $e) {}
        try {} catch (\Exception $e) {}
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(3, $result['App\Foo::run']);
    }

    public function testTryCatchInsideClosureNotCountedForOuterMethod(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Foo {
    public function bar() {
        $f = function () {
            try {} catch (\Throwable $e) {}
        };
    }
}
PHP;
        $result = $this->parseAndCollect($code);
        $this->assertSame(0, $result['App\Foo::bar']);
    }
}
