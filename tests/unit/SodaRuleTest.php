<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Tests;

use Bunnivo\Soda\ComplexityMetrics;
use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Config\SodaRule;
use Bunnivo\Soda\CoreMetrics;
use Bunnivo\Soda\LocMetrics;
use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\EvaluationContext\FileMetrics;
use Bunnivo\Soda\Quality\EvaluationContext\MethodMetricsData;
use Bunnivo\Soda\Quality\EvaluationContext\QualityCore;
use Bunnivo\Soda\Quality\Report\Violation;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Bunnivo\Soda\Result;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(SodaRule::class)]
#[CoversClass(SodaConfig::class)]
#[Small]
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

    // --- helpers ---

    /**
     * @param array<string, array<string, mixed>> $qualityMetrics
     */
    private function context(array $qualityMetrics): EvaluationContext
    {
        $core = new QualityCore($qualityMetrics, []);
        $fileMetrics = new FileMetrics($core, collect(), new MethodMetricsData);

        $config = \Bunnivo\Soda\Quality\QualityConfig::default();

        $loc = new LocMetrics(['directories' => 0, 'files' => 0, 'linesOfCode' => 0, 'commentLinesOfCode' => 0, 'nonCommentLinesOfCode' => 0, 'logicalLinesOfCode' => 0]);
        $complexity = new ComplexityMetrics(['functions' => 0, 'funcLowest' => 0, 'funcAverage' => 0.0, 'funcHighest' => 0, 'classesOrTraits' => 0, 'methods' => 0, 'methodLowest' => 0, 'methodAverage' => 0.0, 'methodHighest' => 0]);
        $result = new Result([], new CoreMetrics($loc, $complexity));

        return new EvaluationContext($config, $result, $fileMetrics);
    }
}

// ---- Test doubles ----

final class ExceedsTestRule extends SodaRule
{
    #[\Override]
    public function id(): string { return 'test_exceeds'; }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        return $this->exceeds($file, $metrics['file_loc'], 500);
    }
}

final class BelowTestRule extends SodaRule
{
    #[\Override]
    public function id(): string { return 'test_below'; }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        return $this->below($file, $metrics['file_loc'], 50);
    }
}

final class ExceedsWithContextRule extends SodaRule
{
    #[\Override]
    public function id(): string { return 'test_exceeds_ctx'; }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        return $this->exceeds($file, $metrics['file_loc'], 500, class: 'MyClass', method: 'myMethod', line: 42);
    }
}

final class SimpleRule extends SodaRule
{
    #[\Override]
    public function id(): string { return 'simple'; }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array { return []; }
}

final class NotARuleChecker
{
    public function check(): Collection { return collect(); }
}
