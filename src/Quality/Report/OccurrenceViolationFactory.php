<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Report;

use Bunnivo\Soda\Quality\Limits;

final class OccurrenceViolationFactory
{
    /**
     * @param array{
     *   rule: string,
     *   file: string,
     *   value: int,
     *   threshold: int,
     *   line?: ?int,
     *   message?: ?string,
     *   class?: ?string,
     *   method?: ?string
     * } $spec
     */
    public static function build(array $spec): Violation
    {
        return ViolationBuilder::of($spec['rule'], $spec['file'], new Limits($spec['value'], $spec['threshold']))
            ->atLine($spec['line'] ?? null)
            ->atClass($spec['class'] ?? null)
            ->atMethod($spec['method'] ?? null)
            ->withMessage($spec['message'] ?? null)
            ->build();
    }
}
