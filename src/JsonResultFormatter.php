<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Breathing\BreathingMetrics;
use Bunnivo\Soda\Structure\Metrics;

/**
 * @psalm-return array<string, mixed>
 */
final readonly class JsonResultFormatter
{
    public function format(Result $result): array
    {
        $data = $this->formatBase($result);
        $structure = $result->structure();

        if (! $structure instanceof Metrics) {
            return $data;
        }

        $data['structure'] = $this->formatStructure($structure);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatBase(Result $result): array
    {
        $data = array_merge(
            $this->formatLoc($result->loc()),
            ['complexity' => $this->formatComplexity($result->complexity())],
            ['errors'     => $result->errorInfo()['errors']],
        );

        $breathing = $result->breathing();
        if ($breathing instanceof BreathingMetrics) {
            $data['breathing'] = $breathing->toArray();
        }

        return $data;
    }

    /**
     * @return array{directories: int, files: int, loc: array<string, mixed>}
     */
    private function formatLoc(LocMetrics $loc): array
    {
        $stats = $loc->stats();
        $pct = $loc->percentages();

        return [
            'directories' => $stats['directories'],
            'files'       => $stats['files'],
            'loc'         => [
                'linesOfCode'           => $stats['linesOfCode'],
                'commentLinesOfCode'    => $stats['commentLinesOfCode'],
                'commentPercentage'     => round($pct['comment'], 2),
                'nonCommentLinesOfCode' => $stats['nonCommentLinesOfCode'],
                'nonCommentPercentage'  => round($pct['nonComment'], 2),
                'logicalLinesOfCode'    => $stats['logicalLinesOfCode'],
                'logicalPercentage'     => round($pct['logical'], 2),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatComplexity(ComplexityMetrics $complexity): array
    {
        $methods = $complexity->methods();
        $functions = $complexity->functions();
        $classes = $complexity->classes();

        return [
            'functions' => [
                'count'   => $functions['count'],
                'lowest'  => $functions['lowest'],
                'average' => round($functions['average'], 2),
                'highest' => $functions['highest'],
            ],
            'methods' => [
                'classesOrTraits' => $methods['classesOrTraits'],
                'count'           => $methods['methods'],
                'lowest'          => $methods['lowest'],
                'average'         => round($methods['average'], 2),
                'highest'         => $methods['highest'],
            ],
            'classes' => [
                'lowest'  => round($classes['lowest'], 2),
                'average' => round($classes['average'], 2),
                'highest' => round($classes['highest'], 2),
            ],
            'averagePerLloc' => round($complexity->averagePerLloc(), 2),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatStructure(Metrics $structure): array
    {
        return array_merge(
            $this->formatStructureCounts($structure),
            [
                'lloc' => [
                    'classes'   => $structure->get('llocClasses'),
                    'functions' => $structure->get('llocFunctions'),
                    'global'    => $structure->get('llocGlobal'),
                ],
                'classLength' => [
                    'min' => $structure->get('classLlocMin'),
                    'avg' => $structure->get('classLlocAvg'),
                    'max' => $structure->get('classLlocMax'),
                ],
                'methodLength' => [
                    'min' => $structure->get('methodLlocMin'),
                    'avg' => $structure->get('methodLlocAvg'),
                    'max' => $structure->get('methodLlocMax'),
                ],
                'methodsPerClass' => [
                    'min' => $structure->get('minimumMethodsPerClass'),
                    'avg' => $structure->get('averageMethodsPerClass'),
                    'max' => $structure->get('maximumMethodsPerClass'),
                ],
                'averageFunctionLength' => $structure->get('averageFunctionLength'),
                'dependencies'          => $this->formatStructureDependencies($structure),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formatStructureCounts(Metrics $structure): array
    {
        $arr = $structure->toArray();

        return [
            'namespaces'              => $arr['namespaces'],
            'interfaces'              => $arr['interfaces'],
            'traits'                  => $arr['traits'],
            'classes'                 => $arr['classes'],
            'abstractClasses'         => $arr['abstractClasses'],
            'concreteClasses'         => $arr['concreteClasses'],
            'finalClasses'            => $arr['finalClasses'],
            'nonFinalClasses'         => $arr['nonFinalClasses'],
            'methods'                 => $arr['methods'],
            'nonStaticMethods'        => $arr['nonStaticMethods'],
            'staticMethods'           => $arr['staticMethods'],
            'publicMethods'           => $arr['publicMethods'],
            'protectedMethods'        => $arr['protectedMethods'],
            'privateMethods'          => $arr['privateMethods'],
            'functions'               => $arr['functions'],
            'namedFunctions'          => $arr['namedFunctions'],
            'anonymousFunctions'      => $arr['anonymousFunctions'],
            'constants'               => $arr['constants'],
            'globalConstants'         => $arr['globalConstants'],
            'classConstants'          => $arr['classConstants'],
            'publicClassConstants'    => $arr['publicClassConstants'],
            'nonPublicClassConstants' => $arr['nonPublicClassConstants'],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function formatStructureDependencies(Metrics $structure): array
    {
        $arr = $structure->toArray();

        return [
            'globalAccesses'              => $arr['globalAccesses'],
            'globalVariableAccesses'      => $arr['globalVariableAccesses'],
            'superGlobalVariableAccesses' => $arr['superGlobalVariableAccesses'],
            'globalConstantAccesses'      => $arr['globalConstantAccesses'],
            'attributeAccesses'           => $arr['attributeAccesses'],
            'nonStaticAttributeAccesses'  => $arr['nonStaticAttributeAccesses'],
            'staticAttributeAccesses'     => $arr['staticAttributeAccesses'],
            'methodCalls'                 => $arr['methodCalls'],
            'nonStaticMethodCalls'        => $arr['nonStaticMethodCalls'],
            'staticMethodCalls'           => $arr['staticMethodCalls'],
        ];
    }
}
