<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Bunnivo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda;

use Bunnivo\Soda\Formatter\JsonResultFormatter;
use Bunnivo\Soda\Structure\Metrics;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonResultFormatter::class)]
#[UsesClass(Result::class)]
#[Small]
final class JsonResultFormatterTest extends TestCase
{
    public function testFormatsResultAsArray(): void
    {
        $result = new Result(
            [],
            new CoreMetrics(
                new LocMetrics([
                    'directories'           => 2,
                    'files'                 => 10,
                    'linesOfCode'           => 1000,
                    'commentLinesOfCode'    => 200,
                    'nonCommentLinesOfCode' => 800,
                    'logicalLinesOfCode'    => 300,
                ]),
                new ComplexityMetrics([
                    'functions'       => 5,
                    'funcLowest'      => 1,
                    'funcAverage'     => 2.0,
                    'funcHighest'     => 4,
                    'classesOrTraits' => 3,
                    'methods'         => 15,
                    'methodLowest'    => 1,
                    'methodAverage'   => 3.0,
                    'methodHighest'   => 8,
                ]),
            ),
        );

        $data = (new JsonResultFormatter())->format($result);

        $this->assertSame(2, $data['directories']);
        $this->assertSame(10, $data['files']);
        $this->assertSame(1000, $data['loc']['linesOfCode']);
        $this->assertSame(300, $data['loc']['logicalLinesOfCode']);
        $this->assertSame(5, $data['complexity']['functions']['count']);
        $this->assertSame(15, $data['complexity']['methods']['count']);
        $this->assertSame([], $data['errors']);
    }

    public function testIncludesStructureWhenPresent(): void
    {
        $result = new Result(
            [],
            new CoreMetrics(
                new LocMetrics([
                    'directories'        => 1, 'files' => 1, 'linesOfCode' => 100,
                    'commentLinesOfCode' => 10, 'nonCommentLinesOfCode' => 90, 'logicalLinesOfCode' => 50,
                ]),
                new ComplexityMetrics([
                    'functions'       => 0, 'funcLowest' => 0, 'funcAverage' => 0.0, 'funcHighest' => 0,
                    'classesOrTraits' => 1, 'methods' => 2, 'methodLowest' => 1, 'methodAverage' => 1.5, 'methodHighest' => 2,
                ]),
            ),
            new ExtendedMetrics(
                new Metrics([
                    'namespaces'                 => 1, 'interfaces' => 0, 'traits' => 0,
                    'abstractClasses'            => 0, 'finalClasses' => 1, 'nonFinalClasses' => 0,
                    'nonStaticMethods'           => 2, 'staticMethods' => 0,
                    'publicMethods'              => 2, 'protectedMethods' => 0, 'privateMethods' => 0,
                    'namedFunctions'             => 0, 'anonymousFunctions' => 0,
                    'globalConstants'            => 0, 'publicClassConstants' => 0, 'nonPublicClassConstants' => 0,
                    'globalVariableAccesses'     => 0, 'superGlobalVariableAccesses' => 0, 'globalConstantAccesses' => 0,
                    'nonStaticAttributeAccesses' => 0, 'staticAttributeAccesses' => 0,
                    'nonStaticMethodCalls'       => 0, 'staticMethodCalls' => 0,
                    'llocClasses'                => 40, 'llocFunctions' => 0, 'llocGlobal' => 10,
                    'classLlocMin'               => 40, 'classLlocAvg' => 40, 'classLlocMax' => 40,
                    'methodLlocMin'              => 10, 'methodLlocAvg' => 20, 'methodLlocMax' => 30,
                    'averageMethodsPerClass'     => 2, 'minimumMethodsPerClass' => 2, 'maximumMethodsPerClass' => 2,
                    'averageFunctionLength'      => 0,
                ]),
                null,
            ),
        );

        $data = (new JsonResultFormatter())->format($result);

        $this->assertArrayHasKey('structure', $data);
        $this->assertSame(1, $data['structure']['namespaces']);
        $this->assertSame(1, $data['structure']['classes']);
        $this->assertSame(40, $data['structure']['lloc']['classes']);
        $this->assertArrayHasKey('dependencies', $data['structure']);
    }

    public function testIncludesErrorsWhenPresent(): void
    {
        $result = new Result(
            ['Parse error in file.php'],
            new CoreMetrics(
                new LocMetrics([
                    'directories'        => 1, 'files' => 1, 'linesOfCode' => 0,
                    'commentLinesOfCode' => 0, 'nonCommentLinesOfCode' => 0, 'logicalLinesOfCode' => 0,
                ]),
                new ComplexityMetrics([
                    'functions'       => 0, 'funcLowest' => 0, 'funcAverage' => 0.0, 'funcHighest' => 0,
                    'classesOrTraits' => 0, 'methods' => 0, 'methodLowest' => 0, 'methodAverage' => 0.0, 'methodHighest' => 0,
                ]),
            ),
        );

        $data = (new JsonResultFormatter())->format($result);

        $this->assertSame(['Parse error in file.php'], $data['errors']);
    }

    public function testOutputIsJsonEncodable(): void
    {
        $result = new Result(
            [],
            new CoreMetrics(
                new LocMetrics([
                    'directories'        => 1, 'files' => 2, 'linesOfCode' => 100,
                    'commentLinesOfCode' => 20, 'nonCommentLinesOfCode' => 80, 'logicalLinesOfCode' => 50,
                ]),
                new ComplexityMetrics([
                    'functions'       => 1, 'funcLowest' => 1, 'funcAverage' => 1.0, 'funcHighest' => 1,
                    'classesOrTraits' => 1, 'methods' => 3, 'methodLowest' => 1, 'methodAverage' => 2.0, 'methodHighest' => 3,
                ]),
            ),
        );

        $data = (new JsonResultFormatter())->format($result);
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertSame(1, $decoded['directories']);
        $this->assertSame(2, $decoded['files']);
    }
}
