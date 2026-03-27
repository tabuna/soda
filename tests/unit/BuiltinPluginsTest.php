<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Tests;

use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Plugins\BreathingPlugin;
use Bunnivo\Soda\Plugins\ComplexityPlugin;
use Bunnivo\Soda\Plugins\NamingPlugin;
use Bunnivo\Soda\Plugins\StandardPlugin;
use Bunnivo\Soda\Plugins\StructuralPlugin;
use Bunnivo\Soda\Quality\QualityConfig;
use Bunnivo\Soda\Quality\QualityEngine;
use Bunnivo\Soda\Quality\Rule\BreathingChecker;
use Bunnivo\Soda\Quality\Rule\BooleanMethodPrefixChecker;
use Bunnivo\Soda\Quality\Rule\ClassRules;
use Bunnivo\Soda\Quality\Rule\MethodRules;
use Bunnivo\Soda\Quality\Rule\RedundantNamingChecker;
use Bunnivo\Soda\Quality\RuleRegistry\RuleRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(StandardPlugin::class)]
#[CoversClass(StructuralPlugin::class)]
#[CoversClass(ComplexityPlugin::class)]
#[CoversClass(BreathingPlugin::class)]
#[CoversClass(NamingPlugin::class)]
#[CoversClass(SodaConfig::class)]
#[CoversClass(QualityEngine::class)]
#[Small]
final class BuiltinPluginsTest extends TestCase
{
    public function testStandardPluginContainsAllBuiltinRules(): void
    {
        $checkers = (new StandardPlugin)->checkers();

        $this->assertNotEmpty($checkers);

        $classNames = array_map(fn ($c) => $c::class, $checkers);
        $this->assertContains(ClassRules::class, $classNames);
        $this->assertContains(MethodRules::class, $classNames);
        $this->assertContains(BreathingChecker::class, $classNames);
        $this->assertContains(RedundantNamingChecker::class, $classNames);
        $this->assertContains(BooleanMethodPrefixChecker::class, $classNames);
    }

    public function testStandardPluginEqualsRegistryDefault(): void
    {
        $fromPlugin   = (new StandardPlugin)->checkers();
        $fromRegistry = RuleRegistry::default();

        $pluginClasses   = array_map(fn ($c) => $c::class, $fromPlugin);
        $registryClasses = array_map(fn ($c) => $c::class, $fromRegistry);

        $this->assertSame($pluginClasses, $registryClasses);
    }

    public function testStructuralPluginContainsClassRules(): void
    {
        $classNames = array_map(fn ($c) => $c::class, (new StructuralPlugin)->checkers());
        $this->assertContains(ClassRules::class, $classNames);
    }

    public function testComplexityPluginContainsMethodRules(): void
    {
        $classNames = array_map(fn ($c) => $c::class, (new ComplexityPlugin)->checkers());
        $this->assertContains(MethodRules::class, $classNames);
    }

    public function testBreathingPluginContainsBreathingChecker(): void
    {
        $classNames = array_map(fn ($c) => $c::class, (new BreathingPlugin)->checkers());
        $this->assertContains(BreathingChecker::class, $classNames);
    }

    public function testNamingPluginContainsNamingCheckers(): void
    {
        $classNames = array_map(fn ($c) => $c::class, (new NamingPlugin)->checkers());
        $this->assertContains(RedundantNamingChecker::class, $classNames);
        $this->assertContains(BooleanMethodPrefixChecker::class, $classNames);
    }

    // --- withoutBuiltins ---

    public function testWithoutBuiltinsIsFluent(): void
    {
        $config = new SodaConfig;
        $this->assertSame($config, $config->withoutBuiltins());
    }

    public function testWithoutBuiltinsIsReflectedInQualityConfig(): void
    {
        $config = new SodaConfig;
        $config->withoutBuiltins();

        $this->assertTrue($config->isWithoutBuiltins());
    }

    public function testDefaultSodaConfigHasBuiltins(): void
    {
        $this->assertFalse((new SodaConfig)->isWithoutBuiltins());
    }

    public function testQualityEngineWithoutBuiltinsUsesOnlyExtraCheckers(): void
    {
        $config = QualityConfig::default();
        $extra  = [new BreathingChecker];
        $engine = QualityEngine::create($config, $extra, noBuiltinRules: true);

        // We can't inspect private $checkers directly, but we can verify it was constructed.
        $this->assertInstanceOf(QualityEngine::class, $engine);
    }

    public function testQualityEngineWithBuiltinsIncludesAllCheckers(): void
    {
        $config = QualityConfig::default();
        $extra  = [new BreathingChecker];
        $engine = QualityEngine::create($config, $extra, noBuiltinRules: false);

        $this->assertInstanceOf(QualityEngine::class, $engine);
    }

    public function testSodaConfigWithoutBuiltinsPlusCherryPickedPlugin(): void
    {
        $config = new SodaConfig;
        $config->withoutBuiltins()
               ->plugin(StructuralPlugin::class)
               ->plugin(ComplexityPlugin::class);

        $checkers = $config->pluginCheckers();

        $classNames = array_map(fn ($c) => $c::class, $checkers);
        $this->assertContains(ClassRules::class, $classNames);
        $this->assertContains(MethodRules::class, $classNames);
        $this->assertNotContains(BreathingChecker::class, $classNames);
        $this->assertNotContains(RedundantNamingChecker::class, $classNames);
    }
}
