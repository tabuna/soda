<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleDefaults\NamingRuleDefaults;

class_exists(NamingRuleDefaults::class);
class_alias(NamingRuleDefaults::class, __NAMESPACE__.'\\NamingRuleDefaults');
