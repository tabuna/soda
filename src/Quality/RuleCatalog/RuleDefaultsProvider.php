<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleCatalog;

/**
 * @internal
 *
 * @return array<string, array{severity: 'error'|'warning', label: string, comparison?: 'min'|'max'}>
 */
interface RuleDefaultsProvider
{
    public function defaults(): array;
}
