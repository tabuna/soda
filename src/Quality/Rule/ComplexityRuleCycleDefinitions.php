<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleBreathing\ComplexityRuleCycleDefinitions;

class_exists(ComplexityRuleCycleDefinitions::class);
class_alias(ComplexityRuleCycleDefinitions::class, __NAMESPACE__.'\\ComplexityRuleCycleDefinitions');
