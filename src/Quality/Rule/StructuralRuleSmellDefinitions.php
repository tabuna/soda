<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleSmellDefinitions;

class_exists(StructuralRuleSmellDefinitions::class);
class_alias(StructuralRuleSmellDefinitions::class, __NAMESPACE__.'\\StructuralRuleSmellDefinitions');
