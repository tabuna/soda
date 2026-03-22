<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleDefaults\LengthRuleDefaults;

class_exists(LengthRuleDefaults::class);
class_alias(LengthRuleDefaults::class, __NAMESPACE__.'\\LengthRuleDefaults');
