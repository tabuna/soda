<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

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

        if ($structure === null) {
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
        $loc = $result->loc();
        $s = $loc->stats();
        $pct = $loc->percentages();
        $c = $result->complexity();
        $m = $c->methods();
        $f = $c->functions();
        $classStats = $c->classes();

        return [
            'directories' => $s['directories'],
            'files'       => $s['files'],
            'loc'         => [
                'linesOfCode'           => $s['linesOfCode'],
                'commentLinesOfCode'    => $s['commentLinesOfCode'],
                'commentPercentage'     => round($pct['comment'], 2),
                'nonCommentLinesOfCode' => $s['nonCommentLinesOfCode'],
                'nonCommentPercentage'  => round($pct['nonComment'], 2),
                'logicalLinesOfCode'    => $s['logicalLinesOfCode'],
                'logicalPercentage'     => round($pct['logical'], 2),
            ],
            'complexity' => [
                'functions' => [
                    'count'   => $f['count'],
                    'lowest'  => $f['lowest'],
                    'average' => round($f['average'], 2),
                    'highest' => $f['highest'],
                ],
                'methods' => [
                    'classesOrTraits' => $m['classesOrTraits'],
                    'count'           => $m['methods'],
                    'lowest'          => $m['lowest'],
                    'average'         => round($m['average'], 2),
                    'highest'         => $m['highest'],
                ],
                'classes' => [
                    'lowest'  => round($classStats['lowest'], 2),
                    'average' => round($classStats['average'], 2),
                    'highest' => round($classStats['highest'], 2),
                ],
                'averagePerLloc' => round($c->averagePerLloc(), 2),
            ],
            'errors' => $result->errorInfo()['errors'],
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
                    'classes'   => $structure->llocClasses(),
                    'functions' => $structure->llocFunctions(),
                    'global'    => $structure->llocGlobal(),
                ],
                'classLength' => [
                    'min' => $structure->classLlocMin(),
                    'avg' => $structure->classLlocAvg(),
                    'max' => $structure->classLlocMax(),
                ],
                'methodLength' => [
                    'min' => $structure->methodLlocMin(),
                    'avg' => $structure->methodLlocAvg(),
                    'max' => $structure->methodLlocMax(),
                ],
                'methodsPerClass' => [
                    'min' => $structure->minimumMethodsPerClass(),
                    'avg' => $structure->averageMethodsPerClass(),
                    'max' => $structure->maximumMethodsPerClass(),
                ],
                'averageFunctionLength' => $structure->averageFunctionLength(),
                'dependencies'          => $this->formatStructureDependencies($structure),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formatStructureCounts(Metrics $structure): array
    {
        return [
            'namespaces'              => $structure->namespaces(),
            'interfaces'              => $structure->interfaces(),
            'traits'                  => $structure->traits(),
            'classes'                 => $structure->classes(),
            'abstractClasses'         => $structure->abstractClasses(),
            'concreteClasses'         => $structure->concreteClasses(),
            'finalClasses'            => $structure->finalClasses(),
            'nonFinalClasses'         => $structure->nonFinalClasses(),
            'methods'                 => $structure->methods(),
            'nonStaticMethods'        => $structure->nonStaticMethods(),
            'staticMethods'           => $structure->staticMethods(),
            'publicMethods'           => $structure->publicMethods(),
            'protectedMethods'        => $structure->protectedMethods(),
            'privateMethods'          => $structure->privateMethods(),
            'functions'               => $structure->functions(),
            'namedFunctions'          => $structure->namedFunctions(),
            'anonymousFunctions'      => $structure->anonymousFunctions(),
            'constants'               => $structure->constants(),
            'globalConstants'         => $structure->globalConstants(),
            'classConstants'          => $structure->classConstants(),
            'publicClassConstants'    => $structure->publicClassConstants(),
            'nonPublicClassConstants' => $structure->nonPublicClassConstants(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function formatStructureDependencies(Metrics $structure): array
    {
        return [
            'globalAccesses'              => $structure->globalAccesses(),
            'globalVariableAccesses'      => $structure->globalVariableAccesses(),
            'superGlobalVariableAccesses' => $structure->superGlobalVariableAccesses(),
            'globalConstantAccesses'      => $structure->globalConstantAccesses(),
            'attributeAccesses'           => $structure->attributeAccesses(),
            'nonStaticAttributeAccesses'  => $structure->nonStaticAttributeAccesses(),
            'staticAttributeAccesses'     => $structure->staticAttributeAccesses(),
            'methodCalls'                 => $structure->methodCalls(),
            'nonStaticMethodCalls'        => $structure->nonStaticMethodCalls(),
            'staticMethodCalls'           => $structure->staticMethodCalls(),
        ];
    }
}
