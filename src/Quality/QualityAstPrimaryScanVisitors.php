<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Complexity\EnumAwareComplexityVisitor as ComplexityProbe;

final readonly class QualityAstPrimaryScanVisitors
{
    public function __construct(
        public ComplexityProbe $cyclomaticScan,
        public QualityMetricsVisitor $structureScan,
        public ControlNestingVisitor $controlNesting,
    ) {}
}
