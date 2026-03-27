<?php

declare(strict_types=1);

use Bunnivo\Soda\Config\SodaConfig;

return static function (SodaConfig $config): void {
    $config->structural()
        ->maxMethodLength(30)
        ->maxClassLength(600)
        ->maxArguments(4)
        ->maxMethodsPerClass(25)
        ->maxFileLoc(500);

    $config->complexity()
        ->maxCyclomaticComplexity(12);
};
