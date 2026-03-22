<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleSurfaceSizeDefinitions;

class_exists(StructuralRuleSurfaceSizeDefinitions::class);
class_alias(StructuralRuleSurfaceSizeDefinitions::class, __NAMESPACE__.'\\StructuralRuleSurfaceSizeDefinitions');
