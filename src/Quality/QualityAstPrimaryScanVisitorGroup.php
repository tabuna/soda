<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Complexity\EnumAwareComplexityVisitor as ComplexityProbe;

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
