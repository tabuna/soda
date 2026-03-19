<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\PhpFileQualityExtractor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhpFileQualityExtractor::class)]
#[Small]
final class PhpFileQualityExtractorTest extends TestCase
{
    public function testExtractReturnsExpectedShape(): void
    {
        $path = __DIR__.'/../quality-fixture/ExampleEnum.php';
        $out = (new PhpFileQualityExtractor)->extract($path);

        $this->assertArrayHasKey('metrics', $out);
        $this->assertArrayHasKey('complexity', $out);
        $this->assertArrayHasKey('nesting', $out);
        $this->assertArrayHasKey('returns', $out);
        $this->assertArrayHasKey('booleanConditions', $out);
        $this->assertArrayHasKey('tryCatch', $out);
        $this->assertArrayHasKey('file_loc', $out['metrics']);
        $this->assertArrayHasKey('breathing', $out['metrics']);
    }
}
