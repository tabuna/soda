<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleDefaults\CountProjectRuleDefaults;

class_exists(CountProjectRuleDefaults::class);
class_alias(CountProjectRuleDefaults::class, __NAMESPACE__.'\\CountProjectRuleDefaults');
