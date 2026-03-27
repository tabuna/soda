<?php

declare(strict_types=1);

use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Config\SodaConfigurator;

class SodaRules extends SodaConfigurator
{
    protected function configure(SodaConfig $config): void
    {
        $config->structural()
            ->maxMethodLength(100)
            ->maxClassLength(500)
            ->maxArguments(3)
            ->maxMethodsPerClass(40)
            ->maxFileLoc(700)
            ->maxPropertiesPerClass(5)
            ->maxPublicMethods(20)
            ->maxDependencies(8)
            ->maxTodoFixmeComments(0)
            ->maxCommentedOutCodeLines(0)
            ->maxEmptyCatchBlocks(0)
            ->maxAskThenTellPatterns(0)
            ->maxEfferentCoupling(10)
            ->maxClassesPerFile(1)
            ->maxNamespaceDepth(5)
            ->maxClassesPerNamespace(20)
            ->maxLayerDominancePercentage(50, 4)
            ->maxTraitsPerClass(10)
            ->maxInterfacesPerClass(5)
            ->maxClassesPerProject(300);

        $config->complexity()
            ->maxCyclomaticComplexity(10)
            ->maxControlNesting(3)
            ->maxWeightedCognitiveDensity(60.0)
            ->maxLogicalComplexityFactor(50.0)
            ->maxReturnStatements(4)
            ->maxBooleanConditions(4)
            ->maxTryCatchBlocks(2);

        $config->breathing()
            ->minCodeBreathingScore(100.0)
            ->minVisualBreathingIndex(70.0)
            ->minIdentifierReadabilityScore(90.0)
            ->minCodeOxygenLevel(100.0);

        $config->naming()
            ->avoidRedundantNaming(80.0)
            ->importBooleanMethodsRule([
                'threshold'  => 0,
                'exceptions' => [
                    'methods' => ['runningUnitTests'],
                ],
            ]);
    }
}

return SodaConfigurator::entry(SodaRules::class);