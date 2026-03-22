<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleDefaults\CountClassPartARuleDefaults;

class_exists(CountClassPartARuleDefaults::class);
class_alias(CountClassPartARuleDefaults::class, __NAMESPACE__.'\\CountClassPartARuleDefaults');
