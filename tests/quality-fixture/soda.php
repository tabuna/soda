<?php

declare(strict_types=1);

use Bunnivo\Soda\Config\SodaConfig;

return static function (SodaConfig $config): void {
    $config->structural()
        ->maxMethodLength(120)
        ->maxClassLength(500)
        ->maxArguments(16)
        ->maxMethodsPerClass(21)
        ->maxFileLoc(400);

    $config->complexity()
        ->maxCyclomaticComplexity(26);
};
