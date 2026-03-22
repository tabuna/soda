<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleNaming\BooleanMethodTypeIndexBuilder;

class_exists(BooleanMethodTypeIndexBuilder::class);
class_alias(BooleanMethodTypeIndexBuilder::class, __NAMESPACE__.'\\BooleanMethodTypeIndexBuilder');
