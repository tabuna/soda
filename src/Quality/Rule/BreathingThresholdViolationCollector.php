<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleBreathing\BreathingThresholdViolationCollector;

class_exists(BreathingThresholdViolationCollector::class);
class_alias(BreathingThresholdViolationCollector::class, __NAMESPACE__.'\\BreathingThresholdViolationCollector');
