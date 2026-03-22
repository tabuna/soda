<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleBreathing\ComplexityRuleDefinitions;

class_exists(ComplexityRuleDefinitions::class);
class_alias(ComplexityRuleDefinitions::class, __NAMESPACE__.'\\ComplexityRuleDefinitions');
