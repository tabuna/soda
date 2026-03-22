<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleDefaults\BreathingRuleDefaults;

class_exists(BreathingRuleDefaults::class);
class_alias(BreathingRuleDefaults::class, __NAMESPACE__.'\\BreathingRuleDefaults');
