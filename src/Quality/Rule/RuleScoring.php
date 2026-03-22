<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleCatalog\RuleScoring;

class_exists(RuleScoring::class);
class_alias(RuleScoring::class, __NAMESPACE__.'\\RuleScoring');
