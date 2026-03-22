<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleCatalog\RuleSpec;

class_exists(RuleSpec::class);
class_alias(RuleSpec::class, __NAMESPACE__.'\\RuleSpec');
