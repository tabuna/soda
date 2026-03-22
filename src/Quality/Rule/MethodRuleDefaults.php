<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleDefaults\MethodRuleDefaults;

class_exists(MethodRuleDefaults::class);
class_alias(MethodRuleDefaults::class, __NAMESPACE__.'\\MethodRuleDefaults');
