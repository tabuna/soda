<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleCatalog\RuleDefinitionFields;

class_exists(RuleDefinitionFields::class);
class_alias(RuleDefinitionFields::class, __NAMESPACE__.'\\RuleDefinitionFields');
