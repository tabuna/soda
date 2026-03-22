<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleCatalog\RuleDefinitionPack;

class_exists(RuleDefinitionPack::class);
class_alias(RuleDefinitionPack::class, __NAMESPACE__.'\\RuleDefinitionPack');
