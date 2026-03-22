<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleCatalog\RuleSpecBuilder;

class_exists(RuleSpecBuilder::class);
class_alias(RuleSpecBuilder::class, __NAMESPACE__.'\\RuleSpecBuilder');
