<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\Config\ConfigResolver;
use Bunnivo\Soda\Quality\ConfigException;
use Bunnivo\Soda\Quality\QualityConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(QualityConfig::class)]
#[Small]
final class QualityConfigTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $config = QualityConfig::default();

        $this->assertSame(80, $config->minScore);
        $this->assertSame(20, $config->getRule('max_method_length'));
        $this->assertSame(500, $config->getRule('max_class_length'));
        $this->assertSame(3, $config->getRule('max_arguments'));
        $this->assertSame(20, $config->getRule('max_methods_per_class'));
        $this->assertSame(400, $config->getRule('max_file_loc'));
        $this->assertSame(10, $config->getRule('max_cyclomatic_complexity'));
    }

    public function testFromFile(): void
    {
        $path = __DIR__.'/../_fixture/code-quality.json';
        $config = QualityConfig::fromFile($path);

        $this->assertSame(75, $config->minScore);
        $this->assertSame(30, $config->getRule('max_method_length'));
        $this->assertSame(600, $config->getRule('max_class_length'));
    }

    public function testFromFileThrowsWhenNotReadable(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Config file not readable');

        QualityConfig::fromFile('/nonexistent/code-quality.json');
    }

    public function testResolveUsesExplicitPath(): void
    {
        $path = __DIR__.'/../_fixture/code-quality.json';
        $config = ConfigResolver::resolveConfig([__FILE__], $path);

        $this->assertSame(75, $config->minScore);
    }

    public function testResolveFindsSodaJsonBeforeCodeQualityJson(): void
    {
        $dir = sys_get_temp_dir().'/soda-resolve-'.uniqid();
        mkdir($dir, 0700, true);
        $sodaPath = $dir.'/soda.json';
        $codeQualityPath = $dir.'/code-quality.json';
        file_put_contents($sodaPath, '{"quality":{"min_score":90},"rules":{}}');
        file_put_contents($codeQualityPath, '{"quality":{"min_score":75},"rules":{}}');

        try {
            $config = ConfigResolver::resolveConfig([$dir.'/dummy.php']);
            $this->assertSame(90, $config->minScore);
        } finally {
            unlink($sodaPath);
            unlink($codeQualityPath);
            rmdir($dir);
        }
    }

    public function testResolveReturnsDefaultWhenNoConfigFound(): void
    {
        $noConfigDir = sys_get_temp_dir().'/soda-no-config-'.uniqid();
        mkdir($noConfigDir, 0700, true);

        try {
            $config = ConfigResolver::resolveConfig([$noConfigDir.'/dummy.php']);
            $this->assertSame(80, $config->minScore);
        } finally {
            rmdir($noConfigDir);
        }
    }

    public function testRuleWithZeroDisablesRule(): void
    {
        $dir = sys_get_temp_dir().'/soda-disable-'.uniqid();
        mkdir($dir, 0700, true);
        $path = $dir.'/soda.json';
        file_put_contents($path, '{"quality":{"min_score":80},"rules":{"max_control_nesting":0,"min_code_breathing_score":0}}');

        try {
            $config = QualityConfig::fromFile($path);
            $this->assertSame(0, $config->getRule('max_control_nesting'));
            $this->assertSame(0, $config->getRule('min_code_breathing_score'));
        } finally {
            unlink($path);
            rmdir($dir);
        }
    }
}
