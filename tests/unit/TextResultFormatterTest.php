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
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextResultFormatter::class)]
#[UsesClass(Result::class)]
#[Small]
final class TextResultFormatterTest extends TestCase
{
    public function testFormatsResultAsText(): void
    {
        $this->assertStringEqualsFile(
            __DIR__.'/../_expectations/result.txt',
            (new TextResultFormatter)->format(
                new Result(
                    [],
                    new CoreMetrics(
                        new LocMetrics([
                            'directories'           => 1,
                            'files'                 => 2,
                            'linesOfCode'           => 10,
                            'commentLinesOfCode'    => 4,
                            'nonCommentLinesOfCode' => 6,
                            'logicalLinesOfCode'    => 3,
                        ]),
                        new ComplexityMetrics([
                            'functions'       => 7,
                            'funcLowest'      => 8,
                            'funcAverage'     => 9.0,
                            'funcHighest'     => 10,
                            'classesOrTraits' => 11,
                            'methods'         => 12,
                            'methodLowest'    => 13,
                            'methodAverage'   => 14.0,
                            'methodHighest'   => 15,
                        ]),
                    ),
                ),
            ),
        );
    }
}
