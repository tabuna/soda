<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleBreathing\ComplexityRuleDensityDefinitions;

class_exists(ComplexityRuleDensityDefinitions::class);
class_alias(ComplexityRuleDensityDefinitions::class, __NAMESPACE__.'\\ComplexityRuleDensityDefinitions');
