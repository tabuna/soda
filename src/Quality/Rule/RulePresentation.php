<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleCatalog\RulePresentation;

class_exists(RulePresentation::class);
class_alias(RulePresentation::class, __NAMESPACE__.'\\RulePresentation');
