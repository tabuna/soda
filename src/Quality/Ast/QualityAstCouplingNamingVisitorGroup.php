<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Ast;

use Bunnivo\Soda\Quality\Naming\RedundantNamingVisitor as NamingProbe;
use Bunnivo\Soda\Quality\Visitor\EfferentCouplingVisitor as CouplingProbe;

/**
 * @internal
 */
final class QualityAstCouplingNamingVisitorGroup
{
    public static function build(): QualityAstCouplingNamingVisitors
    {
        return new QualityAstCouplingNamingVisitors(
            new CouplingProbe,
            new NamingProbe,
        );
    }
}
