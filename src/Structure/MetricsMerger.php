<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Structure;

use function array_merge;

/**
 * @internal
 */
final class MetricsMerger
{
    /**
     * @param array<string, array<string, mixed>> $results
     *
     * @return array<string, mixed>
     */
    public static function merge(array $results): array
    {
        $merged = self::emptyMerged();

        foreach ($results as $r) {
            self::mergeOne($merged, $r);
        }

        $merged['namespaces'] = collect($results)
            ->flatMap(fn (array $r) => array_keys($r['namespaces'] ?? []))
            ->unique()
            ->count();

        return $merged;
    }

    /**
     * @return array<string, mixed>
     */
    private static function emptyMerged(): array
    {
        return [
            'namespaces'                   => 0,
            'interfaces'                   => 0,
            'traits'                       => 0,
            'abstractClasses'              => 0,
            'finalClasses'                 => 0,
            'nonFinalClasses'              => 0,
            'classLines'                   => [],
            'methodLines'                  => [],
            'methodsPerClass'              => [],
            'functionLines'                => [],
            'nonStaticMethods'             => 0,
            'staticMethods'                => 0,
            'publicMethods'                => 0,
            'protectedMethods'             => 0,
            'privateMethods'               => 0,
            'namedFunctions'               => 0,
            'anonymousFunctions'           => 0,
            'globalConstants'              => 0,
            'publicClassConstants'         => 0,
            'nonPublicClassConstants'      => 0,
            'globalVariableAccesses'       => 0,
            'superGlobalVariableAccesses'  => 0,
            'globalConstantAccesses'       => 0,
            'nonStaticAttributeAccesses'   => 0,
            'staticAttributeAccesses'      => 0,
            'nonStaticMethodCalls'         => 0,
            'staticMethodCalls'            => 0,
        ];
    }

    /**
     * @param array<string, mixed> $merged
     * @param array<string, mixed> $r
     */
    private static function mergeOne(array &$merged, array $r): void
    {
        $merged['interfaces'] += $r['interfaces'];
        $merged['traits'] += $r['traits'];
        $merged['abstractClasses'] += $r['abstractClasses'];
        $merged['finalClasses'] += $r['finalClasses'];
        $merged['nonFinalClasses'] += $r['nonFinalClasses'];
        $merged['classLines'] = array_merge($merged['classLines'], $r['classLines']);
        $merged['methodLines'] = array_merge($merged['methodLines'], $r['methodLines']);
        $merged['methodsPerClass'] = array_merge($merged['methodsPerClass'], $r['methodsPerClass']);
        $merged['functionLines'] = array_merge($merged['functionLines'], $r['functionLines']);
        $merged['nonStaticMethods'] += $r['nonStaticMethods'];
        $merged['staticMethods'] += $r['staticMethods'];
        $merged['publicMethods'] += $r['publicMethods'];
        $merged['protectedMethods'] += $r['protectedMethods'];
        $merged['privateMethods'] += $r['privateMethods'];
        $merged['namedFunctions'] += $r['namedFunctions'];
        $merged['anonymousFunctions'] += $r['anonymousFunctions'];
        $merged['globalConstants'] += $r['globalConstants'];
        $merged['publicClassConstants'] += $r['publicClassConstants'];
        $merged['nonPublicClassConstants'] += $r['nonPublicClassConstants'];
        $merged['globalVariableAccesses'] += $r['globalVariableAccesses'];
        $merged['superGlobalVariableAccesses'] += $r['superGlobalVariableAccesses'];
        $merged['globalConstantAccesses'] += $r['globalConstantAccesses'];
        $merged['nonStaticAttributeAccesses'] += $r['nonStaticAttributeAccesses'];
        $merged['staticAttributeAccesses'] += $r['staticAttributeAccesses'];
        $merged['nonStaticMethodCalls'] += $r['nonStaticMethodCalls'];
        $merged['staticMethodCalls'] += $r['staticMethodCalls'];
    }
}
