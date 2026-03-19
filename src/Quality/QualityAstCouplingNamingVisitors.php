<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\Naming\RedundantNamingVisitor;

final readonly class QualityAstCouplingNamingVisitors
{
    public function __construct(
        public EfferentCouplingVisitor $efferentCouplingVisitor,
        public RedundantNamingVisitor $namingVisitor,
    ) {}
}
