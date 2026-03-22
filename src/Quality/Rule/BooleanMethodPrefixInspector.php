<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleNaming\BooleanMethodPrefixInspector;

class_exists(BooleanMethodPrefixInspector::class);
class_alias(BooleanMethodPrefixInspector::class, __NAMESPACE__.'\\BooleanMethodPrefixInspector');
