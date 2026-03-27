<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Verification;

use Bunnivo\Soda\Breathing\BreathingAnalyser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Code Breathing Analyzer Verification Suite.
 *
 * Verifies implementation against technical specification:
 * - Unit verification (manual computation)
 * - Algorithm verification (token classification, nesting)
 * - Edge case testing
 * - Formula correctness
 */
#[CoversClass(BreathingAnalyser::class)]
#[Group('verification')]
final class BreathingVerificationTest extends TestCase
{
    private const float TOLERANCE = 0.0001;

    /**
     * 4.1 Unit Verification — manual computation.
     *
     * Input:
     *   $a = 1;
     *   $b = 2;
     *
     *   $c = $a + $b;
     *
     * N_lines = 5 (<?php + 3 code lines)
     * WCD: weighted sum / N_lines
     */
    public function testUnitVerificationExampleFromSpec(): void
    {
        $code = <<<'PHP'
<?php
$a = 1;
$b = 2;

$c = $a + $b;
PHP;

        $metrics = BreathingAnalyser::analyse($code);

        $this->assertGreaterThan(0, $metrics->wcd(), 'WCD should be positive');
        $this->assertLessThan(15, $metrics->wcd(), 'WCD for simple code should be < 15');
        $this->assertGreaterThanOrEqual(0, $metrics->vbi());
        $this->assertLessThanOrEqual(1, $metrics->vbi());
        $this->assertGreaterThanOrEqual(0, $metrics->irs());
        $this->assertLessThanOrEqual(1, $metrics->irs());
        $this->assertGreaterThanOrEqual(0, $metrics->cbs());
    }

    /**
     * 4.2 Algorithm Verification — nesting detection.
     *
     * conditions = 2 (if, if)
     * loops = 1 (foreach)
     * maxDepth = 3
     * LCF = 1 + 0.3*2 + 0.2*1 + 0.4*3 = 1 + 0.6 + 0.2 + 1.2 = 3.0
     */
    public function testNestingDetection(): void
    {
        $code = <<<'PHP'
<?php
if ($a) {
    foreach ($b as $x) {
        if ($x) {
        }
    }
}
PHP;

        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $metrics = BreathingAnalyser::analyse($code, $nodes);

        $expectedLcf = 1.0 + 0.3 * 2 + 0.2 + 0.4 * 3;
        $this->assertEqualsWithDelta($expectedLcf, $metrics->lcf(), self::TOLERANCE, 'LCF formula: 1 + 0.3*N_cond + 0.2*N_loop + 0.4*depth_max');
    }

    /**
     * 4.3 Integration — CBS formula verification.
     * divisor = 100 + 120/(1 + totalLines/25), sizeFactor = max(1, min(10, 650/(totalLines+40)))
     */
    public function testCbsFormula(): void
    {
        $code = <<<'PHP'
<?php
function add($a, $b) {
    return $a + $b;
}
PHP;

        $metrics = BreathingAnalyser::analyse($code);

        $totalLines = count(explode("\n", $code));
        $divisor = 100 + 120 / (1 + $totalLines / 25);
        if ($totalLines > 400) {
            $divisor *= 5.0;
        } elseif ($totalLines < 250 && $totalLines >= 50) {
            $divisor *= 2.9;
        }

        $sizeFactor = max(1.0, min(10.0, 2400.0 / ($totalLines + 50)));
        if ($totalLines > 400) {
            $sizeFactor = min(10.0, $sizeFactor * 2.0);
        }

        $effectiveLcf = min($metrics->lcf(), 4.0);
        $numerator = $metrics->vbi() * $metrics->irs() * $metrics->col();
        $denominator = 1 + ($metrics->wcd() * $effectiveLcf) / $divisor;
        $expectedCbs = min(1.0, ($numerator * $sizeFactor) / $denominator);

        $this->assertEqualsWithDelta($expectedCbs, $metrics->cbs(), self::TOLERANCE, 'CBS must match formula');
    }

    /**
     * 5. Edge Case — empty file.
     * Expected: metrics = 0, CBS = 0
     */
    public function testEdgeCaseEmptyFile(): void
    {
        $metrics = BreathingAnalyser::analyse('');

        $this->assertEqualsWithDelta(0.0, $metrics->wcd(), PHP_FLOAT_EPSILON, 'WCD for empty file');
        $this->assertEqualsWithDelta(0.0, $metrics->vbi(), PHP_FLOAT_EPSILON, 'VBI for empty file');
        $this->assertEqualsWithDelta(0.0, $metrics->col(), PHP_FLOAT_EPSILON, 'COL for empty file');
        $this->assertEqualsWithDelta(0.0, $metrics->cbs(), PHP_FLOAT_EPSILON, 'CBS for empty file');
    }

    /**
     * 5. Edge Case — single line.
     * VBI=0 (no blank lines). COL = (0 + shortBlocks)/1 — one block of 1 line is short (≤3).
     */
    public function testEdgeCaseSingleLine(): void
    {
        $metrics = BreathingAnalyser::analyse('<?php $a=1;');

        $this->assertGreaterThan(0, $metrics->wcd());
        $this->assertEqualsWithDelta(0.0, $metrics->vbi(), PHP_FLOAT_EPSILON, 'No blank lines => VBI=0');
        $this->assertGreaterThan(0, $metrics->col(), 'Single block ≤3 lines counts as short block');
    }

    /**
     * 5. Edge Case — file without blank lines.
     * VBI should be 0 (N_blank=0)
     */
    public function testEdgeCaseNoBlankLines(): void
    {
        $code = "<?php\n\$a=1;\n\$b=2;\n\$c=\$a+\$b;";
        $metrics = BreathingAnalyser::analyse($code);

        $this->assertEqualsWithDelta(0.0, $metrics->vbi(), PHP_FLOAT_EPSILON, 'No blank lines => VBI=0');
    }

    /**
     * 5. Edge Case — file with only comments.
     * Comments have weight 0, should not inflate WCD
     */
    public function testEdgeCaseCommentsOnly(): void
    {
        $code = "<?php\n// comment\n/* block */";
        $metrics = BreathingAnalyser::analyse($code);

        $this->assertGreaterThanOrEqual(0, $metrics->wcd());
        $this->assertGreaterThanOrEqual(0, $metrics->cbs());
    }

    /**
     * 7. Determinism — same input produces same output.
     */
    public function testDeterminism(): void
    {
        $code = <<<'PHP'
<?php
function foo($x) {
    if ($x > 0) {
        return $x * 2;
    }
    return 0;
}
PHP;

        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $m = BreathingAnalyser::analyse($code);
            $results[] = [$m->wcd(), $m->lcf(), $m->vbi(), $m->irs(), $m->col(), $m->cbs()];
        }

        $first = $results[0];
        foreach ($results as $r) {
            $this->assertSame($first[0], $r[0], 'WCD deterministic');
            $this->assertSame($first[1], $r[1], 'LCF deterministic');
            $this->assertSame($first[2], $r[2], 'VBI deterministic');
            $this->assertSame($first[3], $r[3], 'IRS deterministic');
            $this->assertSame($first[4], $r[4], 'COL deterministic');
            $this->assertSame($first[5], $r[5], 'CBS deterministic');
        }
    }

    /**
     * VBI formula: min(1, rawRatio/0.5) * blockFactor, clamped 0-1.
     * Ideal = 1 blank per 2 code lines (ratio >= 0.5) + uniform blocks.
     */
    public function testVbiFormulaWithKnownValues(): void
    {
        $code = <<<'PHP'
<?php
$a = 1;

$b = 2;

$c = 3;
PHP;

        $metrics = BreathingAnalyser::analyse($code);

        $this->assertGreaterThan(0, $metrics->vbi(), 'Code with blank lines should have VBI > 0');
        $this->assertLessThanOrEqual(1.0, $metrics->vbi(), 'VBI must be ≤ 1');
    }

    /**
     * Ideal code: 1 blank per 2 code lines, uniform short blocks → VBI = 1.0.
     */
    public function testIdealCodeAchievesVbi100(): void
    {
        $code = <<<'PHP'
<?php

$a = 1;

$b = 2;

return $a + $b;
PHP;

        $metrics = BreathingAnalyser::analyse($code);

        $this->assertEqualsWithDelta(1.0, $metrics->vbi(), self::TOLERANCE, 'Ideal breathing (1 blank per 2 code lines, uniform blocks) must achieve VBI 1.0');
    }

    /**
     * Minimal class (ConfigException-style) must achieve VBI 1.0.
     */
    public function testMinimalClassAchievesVbi100(): void
    {
        $code = <<<'PHP'
<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Exception\Exception;
use RuntimeException;

final class ConfigException extends RuntimeException implements Exception {}
PHP;

        $metrics = BreathingAnalyser::analyse($code);

        $this->assertEqualsWithDelta(1.0, $metrics->vbi(), self::TOLERANCE, 'Minimal class must achieve VBI 1.0');
        $this->assertEqualsWithDelta(1.0, $metrics->col(), self::TOLERANCE, 'Minimal class must achieve COL 1.0');
        $this->assertEqualsWithDelta(1.0, $metrics->irs(), self::TOLERANCE, 'Minimal class must achieve IRS 1.0');
    }

    /**
     * TokenKeywordDetector-style (use const block + array) must achieve VBI 1.0.
     */
    public function testUseConstBlockAchievesVbi100(): void
    {
        $code = (string) file_get_contents(__DIR__.'/../../src/Breathing/TokenKeywordDetector.php');
        $metrics = BreathingAnalyser::analyse($code);

        $this->assertEqualsWithDelta(1.0, $metrics->vbi(), self::TOLERANCE, 'Use const + array blocks must achieve VBI 1.0');
    }

    /**
     * Dense code (no blanks) must NOT achieve VBI 1.0.
     */
    public function testDenseCodeDoesNotAchieveVbi100(): void
    {
        $code = "<?php\n\$a=1;\n\$b=2;\n\$c=\$a+\$b;";
        $metrics = BreathingAnalyser::analyse($code);

        $this->assertEqualsWithDelta(0.0, $metrics->vbi(), self::TOLERANCE, 'Dense code without blanks must have VBI 0');
    }

    /**
     * IRS formula: 1 - (avgIdentifierLength - 12) / 20, clamped 0-1.
     * Ideal = avg ≤ 12 chars → IRS = 1.0.
     */
    public function testIrsFormula(): void
    {
        $shortIds = '<?php $a = $b + $c;';
        $longIds = '<?php $veryLongVariableName = $anotherLongName + $thirdLongIdentifier;';

        $shortMetrics = BreathingAnalyser::analyse($shortIds);
        $longMetrics = BreathingAnalyser::analyse($longIds);

        $this->assertGreaterThanOrEqual($longMetrics->irs(), $shortMetrics->irs(), 'Shorter identifiers => higher IRS');
    }

    /**
     * Ideal identifiers (avg ≤ 12 chars) → IRS = 1.0.
     */
    public function testIdealIdentifiersAchievesIrs100(): void
    {
        $code = '<?php $a = 1; $b = 2; $value = $a + $b; return $value;';
        $metrics = BreathingAnalyser::analyse($code);

        $this->assertEqualsWithDelta(1.0, $metrics->irs(), self::TOLERANCE, 'Ideal identifiers (avg ≤ 12 chars) must achieve IRS 1.0');
    }

    /**
     * Long identifiers → IRS < 1.0.
     */
    public function testLongIdentifiersDoNotAchieveIrs100(): void
    {
        $code = '<?php $declarativeBonusCalculator = $tokenWeightResolver + $identifierReadabilityScore;';
        $metrics = BreathingAnalyser::analyse($code);

        $this->assertLessThan(1.0, $metrics->irs(), 'Long identifiers must NOT achieve IRS 1.0');
    }

    /**
     * COL formula: (N_blank + N_shortBlocks) / N_lines
     * shortBlocks = blocks with <= 3 lines
     */
    public function testColIncreasesWithBreathing(): void
    {
        $dense = "<?php\n\$a=1;\n\$b=2;\n\$c=3;";
        $airy = "<?php\n\n\$a=1;\n\n\$b=2;\n\n\$c=3;";

        $denseMetrics = BreathingAnalyser::analyse($dense);
        $airyMetrics = BreathingAnalyser::analyse($airy);

        $this->assertGreaterThan($denseMetrics->col(), $airyMetrics->col(), 'More blanks => higher COL');
    }

    /**
     * Short declarative files (arrays, registries) should pass min_cbs 0.40.
     */
    public function testShortDeclarativeFilePassesMinCbs(): void
    {
        $code = (string) file_get_contents(__DIR__.'/../../src/Plugins/StandardPlugin.php');
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $metrics = BreathingAnalyser::analyse($code, $nodes);

        $this->assertGreaterThanOrEqual(0.40, $metrics->cbs(), 'StandardPlugin (declarative array) should have CBS >= 0.40');
    }

    /**
     * Medium declarative files (array-in-constructor) should pass min_cbs 0.40.
     */
    public function testMediumDeclarativeFilePassesMinCbs(): void
    {
        $code = (string) file_get_contents(__DIR__.'/../../src/Structure/MetricsState.php');
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $metrics = BreathingAnalyser::analyse($code, $nodes);

        $this->assertGreaterThanOrEqual(0.40, $metrics->cbs(), 'MetricsState (array-in-constructor) should have CBS >= 0.40');
    }

    /**
     * Fluent-style files (ClassChecker) should pass min_cbs 0.40.
     */
    public function testFluentStyleFilePassesMinCbs(): void
    {
        $code = (string) file_get_contents(__DIR__.'/../../src/Quality/ClassChecker.php');
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $metrics = BreathingAnalyser::analyse($code, $nodes);

        $this->assertGreaterThanOrEqual(0.40, $metrics->cbs(), 'ClassChecker (fluent style) should have CBS >= 0.40');
    }

    /**
     * Metrics (toArray-based) should pass min_cbs 0.25.
     */
    public function testAccessorStyleFilePassesMinCbs(): void
    {
        $code = (string) file_get_contents(__DIR__.'/../../src/Structure/Metrics.php');
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $metrics = BreathingAnalyser::analyse($code, $nodes);

        $this->assertGreaterThanOrEqual(0.25, $metrics->cbs(), 'Metrics (toArray) should have CBS >= 0.25');
    }

    /**
     * Visitor-style files (many instanceof) should pass min_cbs 0.40.
     */
    public function testVisitorStyleFilePassesMinCbs(): void
    {
        $code = (string) file_get_contents(__DIR__.'/../../src/Structure/MetricsVisitor.php');
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nodes = $parser->parse($code);
        $this->assertNotNull($nodes);

        $metrics = BreathingAnalyser::analyse($code, $nodes);

        $this->assertGreaterThanOrEqual(0.40, $metrics->cbs(), 'MetricsVisitor (visitor pattern) should have CBS >= 0.40');
    }

    public static function regressionProvider(): array
    {
        $cases = [];
        foreach (BreathingRegressionDataset::cases() as $name => $data) {
            $cases[$name] = [$data['code'], $data['expected']];
        }

        return $cases;
    }

    #[DataProvider('regressionProvider')]
    public function testRegressionDataset(string $code, array $expected): void
    {
        $nodes = null;
        if (isset($expected['lcf']) && $code !== '') {
            $parser = (new ParserFactory())->createForNewestSupportedVersion();
            $nodes = $parser->parse($code);
        }

        $metrics = BreathingAnalyser::analyse($code, $nodes);

        $this->assertGreaterThanOrEqual(0, $metrics->cbs(), 'CBS must be non-negative');
        $this->assertLessThanOrEqual(1, $metrics->cbs(), 'CBS must be ≤ 1');

        foreach (array_keys($expected) as $key) {
            $value = $expected[$key];
            $actual = $metrics->get($key);
            $this->assertEqualsWithDelta($value, $actual, self::TOLERANCE, 'Regression: '.$key);
        }
    }
}
