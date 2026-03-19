<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\BooleanConditionsVisitor as BooleanProbe;
use Bunnivo\Soda\Quality\ReturnStatementsVisitor as ReturnProbe;
use Bunnivo\Soda\Quality\TryCatchCountVisitor as CatchProbe;

/**
 * @internal
 */
final class QualityAstFlowScanVisitorGroup
{
    public static function build(): QualityAstFlowScanVisitors
    {
        return new QualityAstFlowScanVisitors(
            new ReturnProbe,
            new BooleanProbe,
            new CatchProbe,
        );
    }
}
