<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Tests;

use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Config\SodaPlugin;
use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(SodaPlugin::class)]
#[CoversClass(SodaConfig::class)]
#[Small]
final class SodaPluginTest extends TestCase
{
    public function testPluginCheckersAreReturnedFromSodaConfig(): void
    {
        $config = new SodaConfig;
        $config->plugin(StubPlugin::class);

        $checkers = $config->pluginCheckers();

        $this->assertCount(1, $checkers);
        $this->assertInstanceOf(StubRuleChecker::class, $checkers[0]);
    }

    public function testMultiplePluginsAreAggregated(): void
    {
        $config = new SodaConfig;
        $config->plugin(StubPlugin::class);
        $config->plugin(AnotherStubPlugin::class);

        $checkers = $config->pluginCheckers();

        $this->assertCount(3, $checkers);
    }

    public function testPluginIsFluent(): void
    {
        $config = new SodaConfig;
        $result = $config->plugin(StubPlugin::class);

        $this->assertSame($config, $result);
    }

    public function testEmptyPluginsReturnsEmptyCheckers(): void
    {
        $config = new SodaConfig;

        $this->assertSame([], $config->pluginCheckers());
    }

    public function testEmptyPluginClassThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty');

        (new SodaConfig)->plugin('');
    }

    public function testNonExistentPluginClassThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');

        (new SodaConfig)->plugin('NonExistentPluginClass');
    }

    public function testClassNotImplementingSodaPluginThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must implement');

        (new SodaConfig)->plugin(NotAPlugin::class);
    }
}

// ---- Test doubles ----

final class StubRuleChecker implements RuleChecker
{
    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        return collect([]);
    }
}

final class StubPlugin implements SodaPlugin
{
    #[\Override]
    public function checkers(): array
    {
        return [new StubRuleChecker];
    }
}

final class AnotherStubPlugin implements SodaPlugin
{
    #[\Override]
    public function checkers(): array
    {
        return [new StubRuleChecker, new StubRuleChecker];
    }
}

final class NotAPlugin
{
    public function checkers(): array
    {
        return [];
    }
}
