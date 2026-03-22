<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Ast;

use Bunnivo\Soda\Complexity\EnumAwareComplexityVisitor as ComplexityProbe;
use Bunnivo\Soda\Quality\Visitor\ControlNestingVisitor;
use Bunnivo\Soda\Quality\Visitor\QualityMetricsVisitor;

use function max;

/**
 * @internal
 */
final class QualityAstPrimaryScanVisitorGroup
{
    private const int LOGICAL_LINES_FLOOR = 0;

    public static function build(int $logicalLinesBaseline): QualityAstPrimaryScanVisitors
    {
        $clampedBaseline = max(self::LOGICAL_LINES_FLOOR, $logicalLinesBaseline);

        return new QualityAstPrimaryScanVisitors(
            new ComplexityProbe(false),
            new QualityMetricsVisitor($clampedBaseline),
            new ControlNestingVisitor,
        );
    }
}
