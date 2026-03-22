<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleBreathing\BreathingRuleDefinitions;

class_exists(BreathingRuleDefinitions::class);
class_alias(BreathingRuleDefinitions::class, __NAMESPACE__.'\\BreathingRuleDefinitions');
