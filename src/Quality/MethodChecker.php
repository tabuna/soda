<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\Flow\CallableRuleViolationAssembler;
use Bunnivo\Soda\Quality\Report\Violation;
use Illuminate\Support\Collection;

final readonly class MethodChecker
{
    public function __construct(
        private QualityConfig $config,
    ) {}

    /**
     * @return Collection<int, Violation>
     */
    public function check(MethodCheckInput $input): Collection
    {
        $assembler = new CallableRuleViolationAssembler($this->config);

        return collect($input->methods)
            ->flatMap(fn (array $callableShape, string $callableName) => $assembler->collectForCallable($input, $callableName, $callableShape))
            ->values();
    }
}
