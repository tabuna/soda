<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleDefaults\BreathingMaxRuleDefaults;

class_exists(BreathingMaxRuleDefaults::class);
class_alias(BreathingMaxRuleDefaults::class, __NAMESPACE__.'\\BreathingMaxRuleDefaults');
