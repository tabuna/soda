<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleCatalog\RuleIdentity;

class_exists(RuleIdentity::class);
class_alias(RuleIdentity::class, __NAMESPACE__.'\\RuleIdentity');
