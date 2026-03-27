<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Plugins;

use Bunnivo\Soda\Config\SodaPlugin;
use Bunnivo\Soda\Quality\Rule\BreathingChecker;

/**
 * Code breathing metrics: visual density, identifier readability, oxygen levels.
 *
 * Covers: min_code_breathing_score, min_visual_breathing_index,
 *         min_identifier_readability_score, min_code_oxygen_level.
 */
final class BreathingPlugin implements SodaPlugin
{
    #[\Override]
    public function checkers(): array
    {
        return [
            new BreathingChecker,
        ];
    }
}
