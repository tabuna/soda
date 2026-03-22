<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleDefaults\CountClassRuleDefaults;

class_exists(CountClassRuleDefaults::class);
class_alias(CountClassRuleDefaults::class, __NAMESPACE__.'\\CountClassRuleDefaults');
