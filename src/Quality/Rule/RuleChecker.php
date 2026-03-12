<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Violation;
use Illuminate\Support\Collection;

interface RuleChecker
{
    /**
     * @return Collection<int, Violation>
     */
    public function check(EvaluationContext $context): Collection;
}
