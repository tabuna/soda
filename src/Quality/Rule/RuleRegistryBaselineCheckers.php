<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleRegistry\RuleRegistryBaselineCheckers;

class_exists(RuleRegistryBaselineCheckers::class);
class_alias(RuleRegistryBaselineCheckers::class, __NAMESPACE__.'\\RuleRegistryBaselineCheckers');
