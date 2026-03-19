<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;

/**
 * Registers all PhpParser visitors used for per-file quality metrics (single place to add a visitor).
 */
final readonly class QualityAstPipeline
{
    public function __construct(
        private QualityAstVisitors $visitors,
    ) {}

    public static function create(int $logicalLines): self
    {
        return new self(QualityAstVisitors::create($logicalLines));
    }

    public function attachTo(NodeTraverser $traverser): void
    {
        $visitorBundle = $this->visitors->astVisitorBundle;
        $primaryScanGroup = $visitorBundle->structuralScan;
        $flowScanGroup = $visitorBundle->flowBranchScan;
        $couplingNamingGroup = $visitorBundle->couplingScan;

        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($primaryScanGroup->cyclomaticScan);
        $traverser->addVisitor($primaryScanGroup->structureScan);
        $traverser->addVisitor($primaryScanGroup->controlNesting);
        $traverser->addVisitor($flowScanGroup->returnStmtScan);
        $traverser->addVisitor($flowScanGroup->booleanCondScan);
        $traverser->addVisitor($flowScanGroup->catchBlockScan);
        $traverser->addVisitor($couplingNamingGroup->efferentCouplingVisitor);
        $traverser->addVisitor($couplingNamingGroup->namingVisitor);
    }

    public function visitors(): QualityAstVisitors
    {
        return $this->visitors;
    }
}
