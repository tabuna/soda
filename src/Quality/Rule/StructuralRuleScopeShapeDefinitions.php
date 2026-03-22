<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleScopeShapeDefinitions;

class_exists(StructuralRuleScopeShapeDefinitions::class);
class_alias(StructuralRuleScopeShapeDefinitions::class, __NAMESPACE__.'\\StructuralRuleScopeShapeDefinitions');
