<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\RuleChecker;
use Bunnivo\Soda\Quality\Violation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuleChecker::class)]
#[Small]
final class RuleCheckerTest extends TestCase
{
    #[DataProvider('noViolationProvider')]
    public function testReturnsEmptyWhenWithinLimit(
        int $value,
        int $limit,
    ): void {
        $result = RuleChecker::whenExceeded('max_class_length')
            ->file('/path/to/file.php')
            ->class('App\Foo')
            ->forValue($value)
            ->limit($limit)
            ->result();

        $this->assertSame([], $result);
    }

    /**
     * @return array<string, array{value: int, limit: int}>
     */
    public static function noViolationProvider(): array
    {
        return [
            'value equals limit' => ['value' => 100, 'limit' => 100],
            'value below limit'  => ['value' => 50, 'limit' => 100],
            'limit disabled'     => ['value' => 1000, 'limit' => 0],
        ];
    }

    public function testReturnsViolationWhenExceeded(): void
    {
        $result = RuleChecker::whenExceeded('max_class_length')
            ->file('/path/to/file.php')
            ->class('App\Foo')
            ->forValue(150)
            ->limit(100)
            ->result();

        $this->assertCount(1, $result);
        $this->assertInstanceOf(Violation::class, $result[0]);
        $this->assertSame('max_class_length', $result[0]->rule);
        $this->assertSame('/path/to/file.php', $result[0]->file);
        $this->assertSame('App\Foo', $result[0]->class());
        $this->assertSame(['value' => 150, 'threshold' => 100], $result[0]->limits());
    }

    public function testSupportsMethodContext(): void
    {
        $result = RuleChecker::whenExceeded('max_method_length')
            ->file('/path/to/file.php')
            ->method('App\Foo::bar')
            ->class('App\Foo')
            ->forValue(50)
            ->limit(10)
            ->result();

        $this->assertCount(1, $result);
        $this->assertSame('App\Foo::bar', $result[0]->method());
        $this->assertSame('App\Foo', $result[0]->class());
    }

    public function testChainableMethodsReturnNewInstance(): void
    {
        $builder = RuleChecker::whenExceeded('max_class_length');

        $withFile = $builder->file('/a.php');
        $this->assertNotSame($builder, $withFile);

        $withClass = $withFile->class('Foo');
        $this->assertNotSame($withFile, $withClass);

        $withValue = $withClass->forValue(10);
        $this->assertNotSame($withClass, $withValue);

        $withLimit = $withValue->limit(5);
        $this->assertNotSame($withValue, $withLimit);
    }

    public function testExtensibilityMethodsDoNotBreakChain(): void
    {
        $result = RuleChecker::whenExceeded('max_class_length')
            ->file('/file.php')
            ->class('Foo')
            ->line(42)
            ->meta('warning', 'Class too long')
            ->forValue(100)
            ->limit(50)
            ->result();

        $this->assertCount(1, $result);
        $this->assertSame('max_class_length', $result[0]->rule);
    }
}
