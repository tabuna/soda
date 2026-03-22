<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleBreathing\BreathingRuleScoreDefinitions;

class_exists(BreathingRuleScoreDefinitions::class);
class_alias(BreathingRuleScoreDefinitions::class, __NAMESPACE__.'\\BreathingRuleScoreDefinitions');
