<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Complexity\EnumAwareComplexityVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Fork sebastian/complexity: upstream не поддерживает PHP 8.1 Enum (assert Class_|Trait_).
 *
 * REMOVE_WHEN sebastian/complexity adds Enum support:
 * 1. Delete EnumAwareComplexityVisitor + this test file
 * 2. Revert FileAnalyser, QualityAnalyser → ComplexityCalculatingVisitor
 * 3. Remove Enum_ from MethodVisitorTrait, ControlNestingVisitor, ReturnStatementsVisitor, BooleanConditionsVisitor
 * 4. Delete tests/quality-fixture/ExampleEnum.php
 * 5. Remove enum-workaround tests from ControlNestingVisitorTest, QualityCommandTest
 *
 * @see https://github.com/sebastianbergmann/complexity
 */
final class EnumAwareComplexityVisitorTest extends TestCase
{
    private function parseAndCollect(string $code): array
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $visitor = new EnumAwareComplexityVisitor(false);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        $result = [];
        foreach ($visitor->result()->asArray() as $item) {
            $result[$item->name()] = $item->cyclomaticComplexity();
        }

        return $result;
    }

    /**
     * @group enum-workaround
     * sebastian/complexity падает с AssertionError на Enum методах.
     */
    #[Group('enum-workaround')]
    public function testEnumMethodsDoNotCrashAndAreIncluded(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
enum Status: string {
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string {
        if ($this === self::Active) {
            return 'Active';
        }
        return 'Inactive';
    }

    public function fromValue(string $v): self {
        return match ($v) {
            'active' => self::Active,
            'inactive' => self::Inactive,
            default => throw new \InvalidArgumentException(),
        };
    }
}
PHP;
        $result = $this->parseAndCollect($code);

        $this->assertArrayHasKey('App\Status::label', $result);
        $this->assertSame(2, $result['App\Status::label']);

        $this->assertArrayHasKey('App\Status::fromValue', $result);
        $this->assertGreaterThanOrEqual(3, $result['App\Status::fromValue']);
    }

    /**
     * @group enum-workaround
     */
    #[Group('enum-workaround')]
    public function testEnumWithClassAndTraitStillWorks(): void
    {
        $code = <<<'PHP'
<?php
namespace App;
enum E { case A; public function m() { return 1; } }
class C { public function m() { if (true) return 1; return 0; } }
trait T { public function m() { return 1; } }
PHP;
        $result = $this->parseAndCollect($code);

        $this->assertArrayHasKey('App\E::m', $result);
        $this->assertArrayHasKey('App\C::m', $result);
        $this->assertArrayHasKey('App\T::m', $result);
    }
}
