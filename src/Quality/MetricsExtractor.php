<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use function array_pop;
use function count;
use function explode;
use function implode;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;

final class MetricsExtractor
{
    /**
     * @return array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, traits: int, interfaces: int, namespace: string, namespace_depth: int}
     */
    public static function extract(Class_|Trait_ $node): array
    {
        [$namespace, $depth] = self::extractNamespace($node);

        return [
            'loc'             => $node->getEndLine() - $node->getStartLine() + 1,
            'methods'         => 0,
            'properties'      => self::countProperties($node),
            'public_methods'  => self::countPublicMethods($node),
            'dependencies'    => self::countDependencies($node),
            'traits'          => self::countTraits($node),
            'interfaces'      => $node instanceof Class_ ? count($node->implements) : 0,
            'namespace'       => $namespace,
            'namespace_depth' => $depth,
        ];
    }

    private static function countProperties(Class_|Trait_ $node): int
    {
        $count = 0;
        foreach ($node->getProperties() as $prop) {
            $count += count($prop->props);
        }

        return $count;
    }

    private static function countPublicMethods(Class_|Trait_ $node): int
    {
        $count = 0;
        foreach ($node->getMethods() as $m) {
            if ($m->isPublic()) {
                $count++;
            }
        }

        return $count;
    }

    private static function countDependencies(Class_|Trait_ $node): int
    {
        foreach ($node->getMethods() as $m) {
            if ($m->name->toLowerString() === '__construct') {
                return count($m->params);
            }
        }

        return 0;
    }

    private static function countTraits(Class_|Trait_ $node): int
    {
        $count = 0;
        foreach ($node->getTraitUses() as $traitUse) {
            $count += count($traitUse->traits);
        }

        return $count;
    }

    /**
     * @return array{0: string, 1: int}
     */
    private static function extractNamespace(Class_|Trait_ $node): array
    {
        if (! isset($node->namespacedName)) {
            return ['', 0];
        }

        $full = $node->namespacedName->toString();
        $parts = explode('\\', $full);
        if (count($parts) <= 1) {
            return ['', 0];
        }

        array_pop($parts);

        return [implode('\\', $parts), count($parts)];
    }
}
