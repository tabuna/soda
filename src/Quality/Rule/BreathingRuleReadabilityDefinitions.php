<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleBreathing\BreathingRuleReadabilityDefinitions;

class_exists(BreathingRuleReadabilityDefinitions::class);
class_alias(BreathingRuleReadabilityDefinitions::class, __NAMESPACE__.'\\BreathingRuleReadabilityDefinitions');
