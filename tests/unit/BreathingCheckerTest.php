<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\QualityConfig;
use Bunnivo\Soda\Quality\QualityEngine;
use Bunnivo\Soda\Quality\Rule\BreathingChecker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(BreathingChecker::class)]
#[Small]
final class BreathingCheckerTest extends TestCase
{
    private static function createResult(): Result
    {
        $loc = new LocMetrics([
            'directories'           => 1,
            'files'                 => 1,
            'linesOfCode'           => 100,
            'commentLinesOfCode'    => 0,
            'nonCommentLinesOfCode' => 100,
            'logicalLinesOfCode'    => 50,
        ]);
        $complexity = new ComplexityMetrics([
            'functions'       => 0,
            'funcLowest'      => 1,
            'funcAverage'     => 1.0,
            'funcHighest'     => 1,
            'classesOrTraits' => 1,
            'methods'         => 1,
            'methodLowest'    => 1,
            'methodAverage'   => 1.0,
            'methodHighest'   => 1,
        ]);

        return new Result([], new CoreMetrics($loc, $complexity), null);
    }

    public function testProducesViolationWhenCbsBelowMin(): void
    {
        $config = new QualityConfig(80, ['min_code_breathing_score' => 40]);
        $engine = new QualityEngine($config, [new BreathingChecker()]);

        $metrics = [
            '/file.php' => [
                'file_loc'      => 50,
                'classes_count' => 1,
                'classes'       => [],
                'methods'       => [],
                'namespaces'    => [],
                'breathing'     => ['wcd' => 10, 'lcf' => 1, 'vbi' => 0.2, 'irs' => 0.9, 'col' => 0.2, 'cbs' => 0.15],
            ],
        ];

        $result = $engine->evaluate(self::createResult(), $metrics, []);

        $this->assertCount(1, $result->violations);
        $this->assertSame('min_code_breathing_score', $result->violations->first()->rule);
    }

    public function testProducesViolationWhenCbsBelowMinLegacyMinCbs(): void
    {
        $config = new QualityConfig(80, ['min_cbs' => 40]);
        $engine = new QualityEngine($config, [new BreathingChecker()]);

        $metrics = [
            '/file.php' => [
                'file_loc'      => 50,
                'classes_count' => 1,
                'classes'       => [],
                'methods'       => [],
                'namespaces'    => [],
                'breathing'     => ['wcd' => 10, 'lcf' => 1, 'vbi' => 0.2, 'irs' => 0.9, 'col' => 0.2, 'cbs' => 0.15],
            ],
        ];

        $result = $engine->evaluate(self::createResult(), $metrics, []);

        $this->assertCount(1, $result->violations);
        $this->assertSame('min_code_breathing_score', $result->violations->first()->rule);
    }

    public function testNoViolationWhenCbsAboveMin(): void
    {
        $config = new QualityConfig(80, ['min_code_breathing_score' => 40]);
        $engine = new QualityEngine($config, [new BreathingChecker()]);

        $metrics = [
            '/file.php' => [
                'file_loc'      => 50,
                'classes_count' => 1,
                'classes'       => [],
                'methods'       => [],
                'namespaces'    => [],
                'breathing'     => ['wcd' => 5, 'lcf' => 1, 'vbi' => 0.5, 'irs' => 0.9, 'col' => 0.4, 'cbs' => 0.65],
            ],
        ];

        $result = $engine->evaluate(self::createResult(), $metrics, []);

        $this->assertCount(0, $result->violations);
    }

    public function testNoViolationWhenMinCbsDisabled(): void
    {
        $config = new QualityConfig(80, ['min_code_breathing_score' => 0]);
        $engine = new QualityEngine($config, [new BreathingChecker()]);

        $metrics = [
            '/file.php' => [
                'file_loc'      => 50,
                'classes_count' => 1,
                'classes'       => [],
                'methods'       => [],
                'namespaces'    => [],
                'breathing'     => ['wcd' => 10, 'lcf' => 1, 'vbi' => 0.1, 'irs' => 0.5, 'col' => 0.1, 'cbs' => 0.05],
            ],
        ];

        $result = $engine->evaluate(self::createResult(), $metrics, []);

        $this->assertCount(0, $result->violations);
    }
}
