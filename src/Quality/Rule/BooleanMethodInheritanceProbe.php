<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleNaming\BooleanMethodInheritanceProbe;

class_exists(BooleanMethodInheritanceProbe::class);
class_alias(BooleanMethodInheritanceProbe::class, __NAMESPACE__.'\\BooleanMethodInheritanceProbe');
