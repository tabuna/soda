<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Ast;

use Bunnivo\Soda\Quality\Naming\RedundantNamingVisitor;
use Bunnivo\Soda\Quality\Visitor\EfferentCouplingVisitor;

final readonly class QualityAstCouplingNamingVisitors
{
    public function __construct(
        public EfferentCouplingVisitor $efferentCouplingVisitor,
        public RedundantNamingVisitor $namingVisitor,
    ) {}
}
