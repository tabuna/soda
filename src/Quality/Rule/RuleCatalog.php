<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleCatalog\RuleCatalog;

class_exists(RuleCatalog::class);
class_alias(RuleCatalog::class, __NAMESPACE__.'\\RuleCatalog');
