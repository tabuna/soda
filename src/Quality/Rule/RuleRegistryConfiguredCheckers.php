<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleRegistry\RuleRegistryConfiguredCheckers;

class_exists(RuleRegistryConfiguredCheckers::class);
class_alias(RuleRegistryConfiguredCheckers::class, __NAMESPACE__.'\\RuleRegistryConfiguredCheckers');
