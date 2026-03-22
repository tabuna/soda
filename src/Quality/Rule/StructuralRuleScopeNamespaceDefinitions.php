<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\RuleStructure\StructuralRuleScopeNamespaceDefinitions;

class_exists(StructuralRuleScopeNamespaceDefinitions::class);
class_alias(StructuralRuleScopeNamespaceDefinitions::class, __NAMESPACE__.'\\StructuralRuleScopeNamespaceDefinitions');
