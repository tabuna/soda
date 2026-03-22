<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleNaming;

use function array_pop;

/**
 * @internal
 */
final class BooleanMethodInheritanceProbe
{
    /**
     * @param array<string, array{inherits: list<string>, methods: array<string, true>}> $typeIndex
     */
    public static function hasDeclaredMethod(array $typeIndex, string $typeName, string $methodName): bool
    {
        $visited = [$typeName => true];
        $pending = $typeIndex[$typeName]['inherits'] ?? [];

        while ($pending !== []) {
            $ancestor = array_pop($pending);
            if (! is_string($ancestor)) {
                continue;
            }

            if (isset($visited[$ancestor])) {
                continue;
            }

            $visited[$ancestor] = true;
            $ancestorType = $typeIndex[$ancestor] ?? null;

            if ($ancestorType === null) {
                continue;
            }

            if (isset($ancestorType['methods'][$methodName])) {
                return true;
            }

            $pending = [...$pending, ...$ancestorType['inherits']];
        }

        return false;
    }
}
