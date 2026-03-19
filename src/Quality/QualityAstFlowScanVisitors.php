<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\BooleanConditionsVisitor as BooleanReader;
use Bunnivo\Soda\Quality\ReturnStatementsVisitor as ReturnAstProbe;
use Bunnivo\Soda\Quality\TryCatchCountVisitor as CatchBlockProbe;

final readonly class QualityAstFlowScanVisitors
{
    public function __construct(
        public ReturnAstProbe $returnStmtScan,
        public BooleanReader $booleanCondScan,
        public CatchBlockProbe $catchBlockScan,
    ) {}
}
