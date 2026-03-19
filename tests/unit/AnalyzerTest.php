<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\QualityResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Analyzer::class)]
#[Small]
final class AnalyzerTest extends TestCase
{
    public function testAnalyzeReturnsQualityResult(): void
    {
        $path = __DIR__.'/../quality-fixture/SimpleClass.php';
        $result = Analyzer::analyze([$path], false, __DIR__.'/../quality-fixture/soda.json');

        $this->assertInstanceOf(QualityResult::class, $result);
        $this->assertTrue($result->violations->isEmpty());
    }

    public function testFluentFileBuilder(): void
    {
        $path = __DIR__.'/../quality-fixture/SimpleClass.php';
        $result = Analyzer::file($path)
            ->config(__DIR__.'/../quality-fixture/soda.json')
            ->run();

        $this->assertInstanceOf(QualityResult::class, $result);
    }
}
