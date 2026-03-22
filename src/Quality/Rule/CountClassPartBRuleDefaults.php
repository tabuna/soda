<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleDefaults\CountClassPartBRuleDefaults;

class_exists(CountClassPartBRuleDefaults::class);
class_alias(CountClassPartBRuleDefaults::class, __NAMESPACE__.'\\CountClassPartBRuleDefaults');
