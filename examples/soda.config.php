<?php

declare(strict_types=1);

/**
 * Скопируйте в корень как `soda.php` или используйте `php soda init`.
 *
 * @see docs/SODA_PHP_CONFIG.md
 */

use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Config\SodaConfigurator;

class SodaRules extends SodaConfigurator
{
    protected function configure(SodaConfig $config): void
    {
        $config->structural()
            ->maxMethodLength(100)
            ->maxClassLength(800)
            ->maxArguments(3);

        $config->complexity()
            ->maxCyclomaticComplexity(15)
            ->maxControlNesting(3);

        $config->breathing()
            ->minCodeBreathingScore(25);

        if (($_ENV['CI'] ?? false) === true || ($_ENV['CI'] ?? '') === 'true') {
            $config->complexity()->maxCyclomaticComplexity(10);
        }
    }
}

return SodaConfigurator::entry(SodaRules::class);
