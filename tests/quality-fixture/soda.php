<?php

declare(strict_types=1);

use Bunnivo\Soda\Config\Soda;
use Bunnivo\Soda\Plugins\Rules\Complexity\MaxCyclomaticComplexity;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxArguments;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxClassLength;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxFileLoc;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxMethodLength;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxMethodsPerClass;

return Soda::configure()
    ->withPlugins([
        new MaxMethodLength(120),
        new MaxClassLength(500),
        new MaxArguments(16),
        new MaxMethodsPerClass(21),
        new MaxFileLoc(400),
        new MaxCyclomaticComplexity(26),
    ]);
