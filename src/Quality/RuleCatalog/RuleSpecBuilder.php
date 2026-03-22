<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleCatalog;

/**
 * @internal
 *
 * @return array<string, array{severity: 'error'|'warning', label: string, comparison?: 'min'|'max'}>
 */
final class RuleSpecBuilder
{
    /**
     * @param list<RuleSpec> $specs
     *
     * @return array<string, array{severity: 'error'|'warning', label: string, comparison?: 'min'|'max'}>
     */
    public static function build(array $specs): array
    {
        $out = [];
        foreach ($specs as $s) {
            $out[$s->key] = $s->toArray();
        }

        return $out;
    }
}
