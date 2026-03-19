<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Complexity;

use function assert;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Trait_;

/**
 * @internal
 */
final class EnumAwareComplexityQualifiedNameBuilder
{
    /** @return non-empty-string */
    public static function forClassMethod(ClassMethod $node): string
    {
        $parent = $node->getAttribute('parent');

        assert($parent instanceof Class_ || $parent instanceof Trait_ || $parent instanceof Enum_);

        if ($parent->getAttribute('parent') instanceof New_) {
            return 'anonymous class';
        }

        assert(isset($parent->namespacedName));
        assert($parent->namespacedName instanceof Name);

        return $parent->namespacedName->toString().'::'.$node->name->toString();
    }

    /** @return non-empty-string */
    public static function forFunction(Function_ $node): string
    {
        assert(isset($node->namespacedName));
        assert($node->namespacedName instanceof Name);

        $functionName = $node->namespacedName->toString();

        assert($functionName !== '');

        return $functionName;
    }
}
