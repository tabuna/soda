<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleCatalog\RuleDefaultsProvider;

class_exists(RuleDefaultsProvider::class);
class_alias(RuleDefaultsProvider::class, __NAMESPACE__.'\\RuleDefaultsProvider');
