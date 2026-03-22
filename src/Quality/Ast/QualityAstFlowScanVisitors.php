<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Ast;

use Bunnivo\Soda\Quality\TellDontAsk\TellDontAskVisitor as TellDontAskProbe;
use Bunnivo\Soda\Quality\Visitor\BooleanConditionsVisitor as BooleanReader;
use Bunnivo\Soda\Quality\Visitor\EmptyCatchVisitor as EmptyCatchProbe;
use Bunnivo\Soda\Quality\Visitor\ReturnStatementsVisitor as ReturnAstProbe;
use Bunnivo\Soda\Quality\Visitor\TryCatchCountVisitor as CatchBlockProbe;

final readonly class QualityAstFlowScanVisitors
{
    public ReturnAstProbe $returnStmtScan;

    public BooleanReader $booleanCondScan;

    public CatchBlockProbe $catchBlockScan;

    public EmptyCatchProbe $emptyCatchScan;

    public TellDontAskProbe $tellDontAskScan;

    public function __construct()
    {
        $this->returnStmtScan = new ReturnAstProbe;
        $this->booleanCondScan = new BooleanReader;
        $this->catchBlockScan = new CatchBlockProbe;
        $this->emptyCatchScan = new EmptyCatchProbe;
        $this->tellDontAskScan = new TellDontAskProbe;
    }
}
