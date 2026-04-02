<?php

declare(strict_types=1);

use Bunnivo\Soda\Config\Soda;
use Bunnivo\Soda\Plugins\Rules\Breathing\MinCodeBreathingScore;
use Bunnivo\Soda\Plugins\Rules\Breathing\MinCodeOxygenLevel;
use Bunnivo\Soda\Plugins\Rules\Breathing\MinIdentifierReadabilityScore;
use Bunnivo\Soda\Plugins\Rules\Breathing\MinVisualBreathingIndex;
use Bunnivo\Soda\Plugins\Rules\Complexity\MaxBooleanConditions;
use Bunnivo\Soda\Plugins\Rules\Complexity\MaxControlNesting;
use Bunnivo\Soda\Plugins\Rules\Complexity\MaxCyclomaticComplexity;
use Bunnivo\Soda\Plugins\Rules\Complexity\MaxLogicalComplexityFactor;
use Bunnivo\Soda\Plugins\Rules\Complexity\MaxReturnStatements;
use Bunnivo\Soda\Plugins\Rules\Complexity\MaxTryCatchBlocks;
use Bunnivo\Soda\Plugins\Rules\Complexity\MaxWeightedCognitiveDensity;
use Bunnivo\Soda\Plugins\Rules\ListOnlyArray\OnlyListArraysAllowed;
use Bunnivo\Soda\Plugins\Rules\Naming\AvoidRedundantNaming;
use Bunnivo\Soda\Plugins\Rules\Naming\BooleanMethodPrefix;
use Bunnivo\Soda\Plugins\Rules\NoUnusedMethods;
use Bunnivo\Soda\Plugins\Rules\NumericArrayIndex\NoNumericArrayIndex;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxArguments;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxClassesPerFile;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxClassesPerNamespace;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxClassesPerProject;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxClassLength;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxDependencies;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxEfferentCoupling;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxFileLoc;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxInterfacesPerClass;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxLayerDominancePercentage;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxMethodLength;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxMethodsPerClass;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxNamespaceDepth;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxPropertiesPerClass;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxPublicMethods;
use Bunnivo\Soda\Plugins\Rules\Structural\MaxTraitsPerClass;
use Bunnivo\Soda\Plugins\Rules\Structural\NoAskThenTellPatterns;
use Bunnivo\Soda\Plugins\Rules\Structural\NoCommentedOutCode;
use Bunnivo\Soda\Plugins\Rules\Structural\NoEmptyCatchBlocks;
use Bunnivo\Soda\Plugins\Rules\Structural\NoTodoFixmeComments;
use Bunnivo\Soda\Plugins\Rules\UselessVariableRule;

return Soda::configure()
    ->withPlugins([
        // Structural
        new MaxFileLoc(700),
        new MaxClassesPerFile(1),
        new MaxMethodLength(100),
        new MaxClassLength(1000),
        new MaxArguments(5),
        new MaxMethodsPerClass(40),
        new MaxPropertiesPerClass(10),
        new MaxPublicMethods(20),
        new MaxDependencies(8),
        new MaxEfferentCoupling(11),
        new MaxTraitsPerClass(10),
        new MaxInterfacesPerClass(5),
        new NoTodoFixmeComments(),
        new NoCommentedOutCode(),
        new OnlyListArraysAllowed(),
        new NoNumericArrayIndex(),
        new NoUnusedMethods(),
        new NoEmptyCatchBlocks(),
        new NoAskThenTellPatterns(),
        new MaxLayerDominancePercentage(50, 4),
        new MaxNamespaceDepth(5),
        new MaxClassesPerNamespace(40),
        new MaxClassesPerProject(300),

        // Complexity
        new MaxCyclomaticComplexity(10),
        new MaxControlNesting(3),
        new MaxReturnStatements(4),
        new MaxBooleanConditions(4),
        new MaxTryCatchBlocks(2),
        new MaxWeightedCognitiveDensity(70),
        new MaxLogicalComplexityFactor(50),

        // Breathing
        new MinCodeBreathingScore(100),
        new MinVisualBreathingIndex(70),
        new MinIdentifierReadabilityScore(90),
        new MinCodeOxygenLevel(100),

        // Naming
        new AvoidRedundantNaming(80),
        new BooleanMethodPrefix(
            ignore: ['runningUnitTests'],
            prefix: ['check'],
        ),

        // Custom
        new UselessVariableRule(),
    ]);
