<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Ast;

use Bunnivo\Soda\Quality\Ast\QualityAstCouplingNamingVisitors as CouplingAstPack;
use Bunnivo\Soda\Quality\Ast\QualityAstFlowScanVisitors as FlowBranchPack;
use Bunnivo\Soda\Quality\Ast\QualityAstPrimaryScanVisitors as PrimaryAstPack;

/**
 * All PhpParser visitors used together for per-file quality metrics.
 */
final readonly class QualityAstVisitorBundle
{
    public function __construct(
        public PrimaryAstPack $structuralScan,
        public FlowBranchPack $flowBranchScan,
        public CouplingAstPack $couplingScan,
    ) {}

    public static function forLogicalLines(int $lineBaseline): self
    {
        return new self(
            QualityAstPrimaryScanVisitorGroup::build($lineBaseline),
            new FlowBranchPack,
            QualityAstCouplingNamingVisitorGroup::build(),
        );
    }
}
