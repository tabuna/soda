<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleCatalog\RuleDefinition;

class_exists(RuleDefinition::class);
class_alias(RuleDefinition::class, __NAMESPACE__.'\\RuleDefinition');
