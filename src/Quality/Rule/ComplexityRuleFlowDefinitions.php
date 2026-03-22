<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleBreathing\ComplexityRuleFlowDefinitions;

class_exists(ComplexityRuleFlowDefinitions::class);
class_alias(ComplexityRuleFlowDefinitions::class, __NAMESPACE__.'\\ComplexityRuleFlowDefinitions');
