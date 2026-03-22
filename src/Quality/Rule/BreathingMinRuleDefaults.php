<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleDefaults\BreathingMinRuleDefaults;

class_exists(BreathingMinRuleDefaults::class);
class_alias(BreathingMinRuleDefaults::class, __NAMESPACE__.'\\BreathingMinRuleDefaults');
