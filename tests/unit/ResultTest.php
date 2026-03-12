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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Result::class)]
#[Small]
final class ResultTest extends TestCase
{
    private static function createResult(array $overrides = []): Result
    {
        $loc = new LocMetrics(array_merge([
            'directories'           => 1,
            'files'                 => 2,
            'linesOfCode'           => 10,
            'commentLinesOfCode'    => 4,
            'nonCommentLinesOfCode' => 6,
            'logicalLinesOfCode'    => 3,
        ], $overrides['loc'] ?? []));
        $complexity = new ComplexityMetrics(array_merge([
            'functions'       => 7,
            'funcLowest'      => 8,
            'funcAverage'     => 9.0,
            'funcHighest'     => 10,
            'classesOrTraits' => 11,
            'methods'         => 12,
            'methodLowest'    => 13,
            'methodAverage'   => 14.0,
            'methodHighest'   => 15,
        ], $overrides['complexity'] ?? []));

        return new Result($overrides['errors'] ?? [], $loc, $complexity);
    }

    public function testMayHaveNoErrors(): void
    {
        $result = self::createResult();
        $info = $result->errorInfo();

        $this->assertFalse($info['hasErrors']);
        $this->assertSame([], $info['errors']);
    }

    public function testMayHaveErrors(): void
    {
        $result = self::createResult(['errors' => ['error']]);
        $info = $result->errorInfo();

        $this->assertTrue($info['hasErrors']);
        $this->assertSame(['error'], $info['errors']);
    }

    public function testHasLocStats(): void
    {
        $result = self::createResult();
        $s = $result->loc()->stats();

        $this->assertSame(1, $s['directories']);
        $this->assertSame(2, $s['files']);
        $this->assertSame(10, $s['linesOfCode']);
        $this->assertSame(4, $s['commentLinesOfCode']);
        $this->assertSame(6, $s['nonCommentLinesOfCode']);
        $this->assertSame(3, $s['logicalLinesOfCode']);
    }

    public function testHasCommentLinesOfCodePercentage(): void
    {
        $result = self::createResult();
        $this->assertSame(40.0, $result->loc()->percentages()['comment']);

        $result = self::createResult([
            'loc' => [
                'directories'        => 1, 'files' => 2, 'linesOfCode' => 0,
                'commentLinesOfCode' => 0, 'nonCommentLinesOfCode' => 0, 'logicalLinesOfCode' => 0,
            ],
        ]);
        $this->assertSame(0.0, $result->loc()->percentages()['comment']);
    }

    public function testHasNonCommentLinesOfCodePercentage(): void
    {
        $result = self::createResult();
        $this->assertSame(60.0, $result->loc()->percentages()['nonComment']);

        $result = self::createResult([
            'loc' => [
                'directories'        => 1, 'files' => 2, 'linesOfCode' => 0,
                'commentLinesOfCode' => 0, 'nonCommentLinesOfCode' => 0, 'logicalLinesOfCode' => 0,
            ],
        ]);
        $this->assertSame(0.0, $result->loc()->percentages()['nonComment']);
    }

    public function testHasLogicalLinesOfCodePercentage(): void
    {
        $result = self::createResult();
        $this->assertSame(30.0, $result->loc()->percentages()['logical']);

        $result = self::createResult([
            'loc' => [
                'directories'        => 1, 'files' => 2, 'linesOfCode' => 0,
                'commentLinesOfCode' => 0, 'nonCommentLinesOfCode' => 0, 'logicalLinesOfCode' => 0,
            ],
        ]);
        $this->assertSame(0.0, $result->loc()->percentages()['logical']);
    }

    public function testHasFunctions(): void
    {
        $result = self::createResult();
        $f = $result->complexity()->functions();

        $this->assertSame(7, $f['count']);
        $this->assertSame(8, $f['lowest']);
        $this->assertSame(9.0, $f['average']);
        $this->assertSame(10, $f['highest']);
    }

    public function testHasClassesOrTraits(): void
    {
        $result = self::createResult();

        $this->assertSame(11, $result->classesOrTraits());
    }

    public function testHasMethods(): void
    {
        $result = self::createResult();
        $m = $result->complexity()->methods();

        $this->assertSame(12, $m['methods']);
        $this->assertSame(13, $m['lowest']);
        $this->assertSame(14.0, $m['average']);
        $this->assertSame(15, $m['highest']);
    }
}
