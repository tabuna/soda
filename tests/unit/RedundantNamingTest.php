<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\Naming\RedundantNamingAnalyser;
use Bunnivo\Soda\Quality\Naming\RedundantNamingVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class RedundantNamingTest extends TestCase
{
    private function parseAndAnalyse(string $code): array
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $visitor = new RedundantNamingVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        $analyser = new RedundantNamingAnalyser(80.0, 4);

        return $analyser->analyse($visitor->result());
    }

    private function parseNaming(string $code): array
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $visitor = new RedundantNamingVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        return $visitor->result();
    }

    /**
     * PostItemCollection → PostCollection
     */
    public function testClassPostItemCollectionSimplifiesToPostCollection(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class PostItemCollection {}
PHP;
        $violations = $this->parseAndAnalyse($code);

        $this->assertCount(1, $violations);
        $this->assertSame('class', $violations[0]['type']);
        $this->assertSame('PostItemCollection', $violations[0]['current']);
        $this->assertSame('PostCollection', $violations[0]['suggested']);
    }

    /**
     * addUserProfileData(UserProfileData $d) → add(UserProfileData $d)
     */
    public function testMethodAddUserProfileDataSimplifiesToAdd(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class UserProfileData {}
class Service {
    public function addUserProfileData(UserProfileData $d): void {}
}
PHP;
        $violations = $this->parseAndAnalyse($code);

        $methodViolations = array_filter($violations, fn (array $v) => $v['type'] === 'method');
        $this->assertNotEmpty($methodViolations);
        $v = reset($methodViolations);
        $this->assertStringContainsString('addUserProfileData', (string) $v['current']);
        $this->assertStringContainsString('add(', (string) $v['suggested']);
    }

    /**
     * getAllOrders(): Order[] → all() или getAll()
     */
    public function testMethodGetAllOrdersSimplifiesToAll(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class Order {}
class OrderRepository {
    /** @return Order[] */
    public function getAllOrders(): array { return []; }
}
PHP;
        $violations = $this->parseAndAnalyse($code);

        $methodViolations = array_filter($violations, fn (array $v) => $v['type'] === 'method');
        $this->assertNotEmpty($methodViolations);
        $v = reset($methodViolations);
        $this->assertStringContainsString('getAllOrders', (string) $v['current']);
        $this->assertStringStartsWith('all', $v['suggested']);
    }

    /**
     * addUser() в UserService → add() (контекст класса)
     */
    public function testAddUserInUserServiceSimplifiesToAdd(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
class UserService {
    public function addUser() {}
    public function hasUser() {}
}
PHP;
        $violations = $this->parseAndAnalyse($code);

        $methodViolations = array_filter($violations, fn (array $v) => $v['type'] === 'method');
        $this->assertCount(2, $methodViolations);
        $names = array_column($methodViolations, 'current');
        $this->assertContains('addUser()', $names);
        $this->assertContains('hasUser()', $names);
    }

    /**
     * addLogger(LoggerInterface $logger) — не трогать (false-positive)
     */
    public function testAddLoggerInterfaceNotTouched(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
interface LoggerInterface {}
class Container {
    public function addLogger(LoggerInterface $logger): void {}
}
PHP;
        $violations = $this->parseAndAnalyse($code);

        $methodViolations = array_filter($violations, fn (array $v) => $v['type'] === 'method');
        $this->assertEmpty($methodViolations);
    }

    public function testNamingVisitorCollectsInheritanceGraphAndOverrideAttribute(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
interface StatusContract {
    public function runningUnitTests(): bool;
}
abstract class BaseStatus {
    public function runningUnitTests(): bool { return true; }
}
final class AppStatus extends BaseStatus implements StatusContract {
    #[\Override]
    public function runningUnitTests(): bool { return false; }
}
PHP;

        $naming = $this->parseNaming($code);

        $this->assertArrayHasKey('types', $naming);
        $types = [];
        foreach ($naming['types'] as $type) {
            $types[$type['name']] = $type;
        }

        $this->assertSame(['runningUnitTests'], $types['App\StatusContract']['methods']);
        $this->assertSame(['App\BaseStatus', 'App\StatusContract'], $types['App\AppStatus']['inherits']);

        $methods = [];
        foreach ($naming['methods'] as $method) {
            $methods[$method['name']] = $method;
        }

        $this->assertTrue($methods['App\AppStatus::runningUnitTests']['hasOverrideAttribute']);
    }
}
