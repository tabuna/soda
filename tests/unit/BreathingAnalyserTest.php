<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Breathing\BreathingAnalyser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class BreathingAnalyserTest extends TestCase
{
    public function testAnalysesSimpleCode(): void
    {
        $code = <<<'PHP'
<?php

function foo(): int
{
    return 1;
}
PHP;

        $metrics = BreathingAnalyser::analyse($code);

        $this->assertGreaterThan(0, $metrics->wcd());
        $this->assertGreaterThanOrEqual(1.0, $metrics->lcf());
        $this->assertGreaterThanOrEqual(0, $metrics->vbi());
        $this->assertLessThanOrEqual(1.0, $metrics->vbi());
        $this->assertGreaterThanOrEqual(0, $metrics->irs());
        $this->assertLessThanOrEqual(1.0, $metrics->irs());
        $this->assertGreaterThanOrEqual(0, $metrics->col());
        $this->assertGreaterThanOrEqual(0, $metrics->cbs());
    }

    public function testToArrayReturnsRoundedValues(): void
    {
        $code = '<?php $x = 1;';
        $metrics = BreathingAnalyser::analyse($code);

        $arr = $metrics->toArray();

        $this->assertArrayHasKey('wcd', $arr);
        $this->assertArrayHasKey('lcf', $arr);
        $this->assertArrayHasKey('vbi', $arr);
        $this->assertArrayHasKey('irs', $arr);
        $this->assertArrayHasKey('col', $arr);
        $this->assertArrayHasKey('cbs', $arr);
        $this->assertIsFloat($arr['cbs']);
    }

    public function testDenseCodeHasLowerCbs(): void
    {
        $spacious = <<<'PHP'
<?php

function a(): int
{
    return 1;
}

function b(): int
{
    return 2;
}
PHP;

        $dense = <<<'PHP'
<?php
function a(){return 1;}function b(){return 2;}
PHP;

        $spaciousMetrics = BreathingAnalyser::analyse($spacious);
        $denseMetrics = BreathingAnalyser::analyse($dense);

        $this->assertGreaterThan($denseMetrics->cbs(), $spaciousMetrics->cbs());
    }

    public function testComplexCodeHasHigherLcf(): void
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        $simple = '<?php return 1;';
        $simpleNodes = $parser->parse($simple);
        $this->assertNotNull($simpleNodes);

        $complex = <<<'PHP'
<?php
if ($a) {
    foreach ($x as $y) {
        if ($b) {
            switch ($c) {
                case 1: break;
            }
        }
    }
}
PHP;
        $complexNodes = $parser->parse($complex);
        $this->assertNotNull($complexNodes);

        $simpleMetrics = BreathingAnalyser::analyse($simple, $simpleNodes);
        $complexMetrics = BreathingAnalyser::analyse($complex, $complexNodes);

        $this->assertGreaterThan($simpleMetrics->lcf(), $complexMetrics->lcf());
    }
}
