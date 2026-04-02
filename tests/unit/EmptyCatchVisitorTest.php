<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\Visitor\EmptyCatchVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class EmptyCatchVisitorTest extends TestCase
{
    private function parseAndCollect(string $code): array
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $visitor = new EmptyCatchVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        return $visitor->result();
    }

    public function testCollectsEmptyCatchWithMethodContext(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Worker {
    public function run(): void {
        try {} catch (\Throwable $e) {}
    }
}
PHP;

        $result = $this->parseAndCollect($code);

        $this->assertCount(1, $result);
        $this->assertSame(5, $result[0]['line']);
        $this->assertSame('App\Worker', $result[0]['class']);
        $this->assertSame('App\Worker::run', $result[0]['method']);
    }

    public function testIgnoresCatchBlocksWithBody(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Worker {
    public function run(): void {
        try {} catch (\Throwable $e) { report($e); }
    }
}
PHP;

        $this->assertSame([], $this->parseAndCollect($code));
    }
}
