<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda;

use Bunnivo\Soda\Breathing\BreathingMetrics;
use Bunnivo\Soda\Structure\Metrics;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[UsesClass(Result::class)]
#[UsesClass(Metrics::class)]
final class ProjectMetricsTest extends TestCase
{
    public function testAnalysesFiles(): void
    {
        $result = (new ProjectMetrics)->analyse(
            [
                __DIR__.'/../_fixture/example_function.php',
                __DIR__.'/../_fixture/ExampleClass.php',
                __DIR__.'/../_fixture/ExampleInterface.php',
                __DIR__.'/../_fixture/ExampleTrait.php',
            ],
            false,
        );

        $this->assertFalse($result->errorInfo()['hasErrors']);
        $loc = $result->loc()->stats();
        $this->assertSame(1, $loc['directories']);
        $this->assertSame(4, $loc['files']);
        $this->assertSame(164, $loc['linesOfCode']);
        $this->assertSame(32, $loc['commentLinesOfCode']);
        $this->assertSame(132, $loc['nonCommentLinesOfCode']);
        $this->assertSame(40, $loc['logicalLinesOfCode']);
        $this->assertSame(1, $result->complexity()->functions()['count']);
        $this->assertSame(2, $result->classesOrTraits());
        $this->assertSame(2, $result->complexity()->methods()['methods']);

        $breathing = $result->breathing();
        $this->assertInstanceOf(BreathingMetrics::class, $breathing);
        $this->assertGreaterThanOrEqual(0, $breathing->cbs());

        $structure = $result->structure();
        $this->assertInstanceOf(Metrics::class, $structure);
        $arr = $structure->toArray();
        $this->assertSame(1, $arr['namespaces']);
        $this->assertSame(1, $arr['interfaces']);
        $this->assertSame(1, $arr['traits']);
        $this->assertSame(1, $arr['classes']);
        $this->assertGreaterThanOrEqual(2, $arr['methods']);
    }
}
