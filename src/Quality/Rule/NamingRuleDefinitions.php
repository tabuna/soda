<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleStructure\NamingRuleDefinitions;

class_exists(NamingRuleDefinitions::class);
class_alias(NamingRuleDefinitions::class, __NAMESPACE__.'\\NamingRuleDefinitions');
