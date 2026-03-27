<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Plugins;

use Bunnivo\Soda\Config\SodaPlugin;
use Bunnivo\Soda\Quality\Rule\BooleanMethodPrefixChecker;
use Bunnivo\Soda\Quality\Rule\RedundantNamingChecker;

/**
 * Naming convention rules: redundant naming, boolean method prefixes.
 *
 * Covers: max_redundant_naming, boolean_methods_without_prefix.
 */
final class NamingPlugin implements SodaPlugin
{
    #[\Override]
    public function checkers(): array
    {
        return [
            new RedundantNamingChecker,
            new BooleanMethodPrefixChecker,
        ];
    }
}
