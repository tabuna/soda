<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\EfferentCouplingVisitor as CouplingProbe;
use Bunnivo\Soda\Quality\Naming\RedundantNamingVisitor as NamingProbe;

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
