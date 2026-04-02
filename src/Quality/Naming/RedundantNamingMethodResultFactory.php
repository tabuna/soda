<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Naming;

use function collect;
use function ltrim;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;

use function strtolower;

/**
 * @internal
 */
final class RedundantNamingMethodResultFactory
{
    /**
     * @return array{name: string, methodName: string, class: string|null, firstParamType: string|null, returnType: string|null, line: int, isPublic: bool, hasOverrideAttribute: bool}
     */
    public static function fromClassMethod(ClassMethod $node, ?string $class): array
    {
        $methodName = $node->name->toString();
        $fullName = $class !== null ? $class.'::'.$methodName : $methodName;

        return [
            'name'                 => $fullName,
            'methodName'           => $methodName,
            'class'                => $class,
            'firstParamType'       => self::firstParamType($node->params),
            'returnType'           => self::returnTypeLabel($node->getReturnType()),
            'line'                 => $node->getStartLine(),
            'isPublic'             => $node->isPublic(),
            'hasOverrideAttribute' => self::hasOverrideAttribute($node->attrGroups),
        ];
    }

    /**
     * @return array{name: string, methodName: string, class: string|null, firstParamType: string|null, returnType: string|null, line: int, isPublic: bool, hasOverrideAttribute: bool}|null
     */
    public static function fromFunction(Function_ $node): ?array
    {
        $name = $node->namespacedName?->toString();

        if ($name === null || $name === '') {
            return null;
        }

        return [
            'name'                 => $name,
            'methodName'           => $name,
            'class'                => null,
            'firstParamType'       => self::firstParamType($node->params),
            'returnType'           => self::returnTypeLabel($node->getReturnType()),
            'line'                 => $node->getStartLine(),
            'isPublic'             => true,
            'hasOverrideAttribute' => false,
        ];
    }

    /**
     * @param list<AttributeGroup> $attributeGroups
     */
    private static function hasOverrideAttribute(array $attributeGroups): bool
    {
        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if (strtolower(ltrim($attribute->name->toString(), '\\')) === 'override') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param list<Node\Param> $params
     */
    private static function firstParamType(array $params): ?string
    {
        $first = collect($params)->first();

        if ($first === null || $first->type === null) {
            return null;
        }

        return RedundantNamingPhpTypeLabel::fromTypeNode($first->type);
    }

    private static function returnTypeLabel(Node\Identifier|Node\Name|Node\ComplexType|null $returnType): ?string
    {
        return $returnType !== null ? RedundantNamingPhpTypeLabel::fromTypeNode($returnType) : null;
    }
}
