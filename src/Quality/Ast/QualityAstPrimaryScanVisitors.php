<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Ast;

use Bunnivo\Soda\Complexity\EnumAwareComplexityVisitor as ComplexityProbe;
use Bunnivo\Soda\Quality\Visitor\ControlNestingVisitor;
use Bunnivo\Soda\Quality\Visitor\QualityMetricsVisitor;

final readonly class QualityAstPrimaryScanVisitors
{
    public function __construct(
        public ComplexityProbe $cyclomaticScan,
        public QualityMetricsVisitor $structureScan,
        public ControlNestingVisitor $controlNesting,
    ) {}
}
