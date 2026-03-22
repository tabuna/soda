<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleSurfaceApiDefinitions;

class_exists(StructuralRuleSurfaceApiDefinitions::class);
class_alias(StructuralRuleSurfaceApiDefinitions::class, __NAMESPACE__.'\\StructuralRuleSurfaceApiDefinitions');
