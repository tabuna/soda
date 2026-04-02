<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Tests;

use Bunnivo\Soda\ComplexityMetrics;
use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Config\SodaRule;
use Bunnivo\Soda\Config\ViolationAt;
use Bunnivo\Soda\CoreMetrics;
use Bunnivo\Soda\LocMetrics;
use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\EvaluationContext\FileMetrics;
use Bunnivo\Soda\Quality\EvaluationContext\MethodMetricsData;
use Bunnivo\Soda\Quality\EvaluationContext\QualityCore;
use Bunnivo\Soda\Quality\QualityConfig;
use Bunnivo\Soda\Quality\Report\Violation;
use Bunnivo\Soda\Result;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPUnit\Framework\TestCase;

final class SodaRuleTest extends TestCase
{
    // --- SodaRule base class ---

    public function testExceedsReturnsViolationWhenValueOverLimit(): void
    {
        $rule = new ExceedsTestRule;
        $violations = $rule->check($this->context(['file.php' => ['file_loc' => 600]]));

        $this->assertCount(1, $violations);
        $this->assertSame('test_exceeds', $violations->first()->rule);
        $this->assertSame('file.php', $violations->first()->file);
    }

    public function testExceedsReturnsEmptyWhenValueAtLimit(): void
    {
        $rule = new ExceedsTestRule;
        $violations = $rule->check($this->context(['file.php' => ['file_loc' => 500]]));

        $this->assertCount(0, $violations);
    }

    public function testBelowReturnsViolationWhenValueUnderLimit(): void
    {
        $rule = new BelowTestRule;
        $violations = $rule->check($this->context(['file.php' => ['file_loc' => 30]]));

        $this->assertCount(1, $violations);
        $this->assertSame('test_below', $violations->first()->rule);
    }

    public function testBelowReturnsEmptyWhenValueAtLimit(): void
    {
        $rule = new BelowTestRule;
        $violations = $rule->check($this->context(['file.php' => ['file_loc' => 50]]));

        $this->assertCount(0, $violations);
    }

    public function testIteratesMultipleFiles(): void
    {
        $rule = new ExceedsTestRule;
        $violations = $rule->check($this->context([
            'a.php' => ['file_loc' => 600],
            'b.php' => ['file_loc' => 200],
            'c.php' => ['file_loc' => 700],
        ]));

        $this->assertCount(2, $violations);
    }

    public function testExceedsPassesClassAndMethodToViolation(): void
    {
        $rule = new ExceedsWithContextRule;
        $violations = $rule->check($this->context(['file.php' => ['file_loc' => 600]]));

        $this->assertCount(1, $violations);
        $this->assertSame('MyClass', $violations->first()->class());
        $this->assertSame('myMethod', $violations->first()->method());
        $this->assertSame(42, $violations->first()->line());
    }

    // --- SodaConfig::rule() ---

    public function testDirectRuleRegistration(): void
    {
        $config = new SodaConfig;
        $config->rule(SimpleRule::class);

        $checkers = $config->pluginCheckers();

        $this->assertCount(1, $checkers);
        $this->assertInstanceOf(SimpleRule::class, $checkers[0]);
    }

    public function testRuleIsFluent(): void
    {
        $config = new SodaConfig;
        $this->assertSame($config, $config->rule(SimpleRule::class));
    }

    public function testMultipleRulesAreAggregated(): void
    {
        $config = new SodaConfig;
        $config->rule(SimpleRule::class)->rule(SimpleRule::class);

        $this->assertCount(2, $config->pluginCheckers());
    }

    public function testEmptyRuleClassThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty');

        (new SodaConfig)->rule('');
    }

    public function testNonExistentRuleClassThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');

        (new SodaConfig)->rule('Nonexistent\\Rule');
    }

    public function testClassNotImplementingRuleCheckerThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must implement');

        (new SodaConfig)->rule(NotARuleChecker::class);
    }

    // --- analyze() hook ---

    public function testAnalyzeOutputIsMergedIntoMetrics(): void
    {
        $rule = new CustomMetricRule;
        $violations = $rule->check($this->context(['file.php' => ['file_loc' => 10]]));

        $this->assertCount(1, $violations);
        $this->assertSame('custom_metric', $violations->first()->rule);
    }

    public function testAnalyzeOutputOverridesBuiltinMetricWithSameKey(): void
    {
        $rule = new OverrideBuiltinMetricRule;
        $violations = $rule->check($this->context(['file.php' => ['file_loc' => 10]]));

        // Rule overrides file_loc to 999 via analyze(), should trigger violation
        $this->assertCount(1, $violations);
    }

    public function testDefaultAnalyzeReturnsEmptyArray(): void
    {
        $rule = new SimpleRule;
        // No analyze() override — should not crash
        $violations = $rule->check($this->context(['file.php' => ['file_loc' => 10]]));
        $this->assertCount(0, $violations);
    }

    // --- contents() helper ---

    public function testContentsReadsFileContent(): void
    {
        $file = $this->tempFile('<?php echo 1;');
        $rule = new ContentsCapturingRule;
        $rule->check($this->context([$file => ['file_loc' => 1]]));

        $this->assertSame('<?php echo 1;', $rule->captured);
        unlink($file);
    }

    public function testContentsReturnsEmptyStringForMissingFile(): void
    {
        $rule = new ContentsCapturingRule;
        $rule->check($this->context(['/nonexistent/file.php' => ['file_loc' => 1]]));

        $this->assertSame('', $rule->captured);
    }

    // --- parse() helper ---

    public function testParseReturnsAstNodes(): void
    {
        $file = $this->tempFile('<?php class Foo {}');
        $rule = new ParseCapturingRule;
        $rule->check($this->context([$file => ['file_loc' => 1]]));

        $this->assertNotEmpty($rule->captured);
        $this->assertInstanceOf(Class_::class, $rule->captured[0]);
        unlink($file);
    }

    public function testParseReturnsEmptyForInvalidPhp(): void
    {
        $file = $this->tempFile('<?php >>>invalid syntax<<<');
        $rule = new ParseCapturingRule;
        $rule->check($this->context([$file => ['file_loc' => 1]]));

        $this->assertSame([], $rule->captured);
        unlink($file);
    }

    public function testFullCustomMetricsWorkflow(): void
    {
        // Simulate a rule that counts 'var_dump' occurrences from raw file
        $file = $this->tempFile('<?php var_dump($x); var_dump($y); echo 1;');
        $rule = new VarDumpCountRule;
        $violations = $rule->check($this->context([$file => ['file_loc' => 3]]));

        $this->assertCount(1, $violations);
        $this->assertSame('no_var_dump', $violations->first()->rule);
        unlink($file);
    }

    // --- helpers ---

    /**
     * @param array<string, array<string, mixed>> $qualityMetrics
     */
    private function context(array $qualityMetrics): EvaluationContext
    {
        $core = new QualityCore($qualityMetrics, []);
        $fileMetrics = new FileMetrics($core, collect(), new MethodMetricsData);

        $config = QualityConfig::default();

        $loc = new LocMetrics(['directories' => 0, 'files' => 0, 'linesOfCode' => 0, 'commentLinesOfCode' => 0, 'nonCommentLinesOfCode' => 0, 'logicalLinesOfCode' => 0]);
        $complexity = new ComplexityMetrics(['functions' => 0, 'funcLowest' => 0, 'funcAverage' => 0.0, 'funcHighest' => 0, 'classesOrTraits' => 0, 'methods' => 0, 'methodLowest' => 0, 'methodAverage' => 0.0, 'methodHighest' => 0]);
        $result = new Result([], new CoreMetrics($loc, $complexity));

        return new EvaluationContext($config, $result, $fileMetrics);
    }

    private function tempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'soda_rule_test_');
        assert($path !== false);
        file_put_contents($path, $contents);

        return $path;
    }
}

// ---- Test doubles ----

final class ExceedsTestRule extends SodaRule
{
    #[\Override]
    public function id(): string
    {
        return 'test_exceeds';
    }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        return $this->exceeds($file, $metrics['file_loc'], 500);
    }
}

final class BelowTestRule extends SodaRule
{
    #[\Override]
    public function id(): string
    {
        return 'test_below';
    }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        return $this->below($file, $metrics['file_loc'], 50);
    }
}

final class ExceedsWithContextRule extends SodaRule
{
    #[\Override]
    public function id(): string
    {
        return 'test_exceeds_ctx';
    }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        return $this->exceeds($file, $metrics['file_loc'], 500, new ViolationAt(class: 'MyClass', method: 'myMethod', line: 42));
    }
}

final class SimpleRule extends SodaRule
{
    #[\Override]
    public function id(): string
    {
        return 'simple';
    }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        return [];
    }
}

final class CustomMetricRule extends SodaRule
{
    #[\Override]
    public function id(): string
    {
        return 'custom_metric';
    }

    #[\Override]
    protected function analyze(string $file): array
    {
        return ['my_count' => 10];
    }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        return $this->exceeds($file, $metrics['my_count'], 5);
    }
}

final class OverrideBuiltinMetricRule extends SodaRule
{
    #[\Override]
    public function id(): string
    {
        return 'override_builtin';
    }

    #[\Override]
    protected function analyze(string $file): array
    {
        return ['file_loc' => 999]; // overrides the built-in file_loc = 10
    }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        return $this->exceeds($file, $metrics['file_loc'], 500);
    }
}

final class ContentsCapturingRule extends SodaRule
{
    public string $captured = '';

    #[\Override]
    public function id(): string
    {
        return 'contents_capture';
    }

    #[\Override]
    protected function analyze(string $file): array
    {
        $this->captured = $this->contents($file);

        return [];
    }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        return [];
    }
}

final class ParseCapturingRule extends SodaRule
{
    /** @var list<Node> */
    public array $captured = [];

    #[\Override]
    public function id(): string
    {
        return 'parse_capture';
    }

    #[\Override]
    protected function analyze(string $file): array
    {
        $this->captured = $this->parse($file);

        return [];
    }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        return [];
    }
}

final class VarDumpCountRule extends SodaRule
{
    #[\Override]
    public function id(): string
    {
        return 'no_var_dump';
    }

    #[\Override]
    protected function analyze(string $file): array
    {
        preg_match_all('/var_dump\s*\(/', $this->contents($file), $m);

        return ['var_dump_count' => count($m[0])];
    }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        return $this->exceeds($file, $metrics['var_dump_count'], 0);
    }
}

final class NotARuleChecker
{
    public function check(): Collection
    {
        return collect();
    }
}
