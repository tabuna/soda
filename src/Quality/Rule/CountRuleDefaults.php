<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleDefaults\CountRuleDefaults;

class_exists(CountRuleDefaults::class);
class_alias(CountRuleDefaults::class, __NAMESPACE__.'\\CountRuleDefaults');
