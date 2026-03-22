<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleDefaults\StructureRuleDefaults;

class_exists(StructureRuleDefaults::class);
class_alias(StructureRuleDefaults::class, __NAMESPACE__.'\\StructureRuleDefaults');
