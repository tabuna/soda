<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleLengthDefinitions;

class_exists(StructuralRuleLengthDefinitions::class);
class_alias(StructuralRuleLengthDefinitions::class, __NAMESPACE__.'\\StructuralRuleLengthDefinitions');
