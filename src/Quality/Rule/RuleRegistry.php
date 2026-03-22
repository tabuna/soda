<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleRegistry\RuleRegistry;

class_exists(RuleRegistry::class);
class_alias(RuleRegistry::class, __NAMESPACE__.'\\RuleRegistry');
