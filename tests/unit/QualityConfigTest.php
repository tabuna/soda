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

        $this->assertSame(100, $config->getRule('max_method_length'));
        $this->assertSame(500, $config->getRule('max_class_length'));
        $this->assertSame(3, $config->getRule('max_arguments'));
        $this->assertSame(40, $config->getRule('max_methods_per_class'));
        $this->assertSame(700, $config->getRule('max_file_loc'));
        $this->assertSame(8, $config->getRule('max_cyclomatic_complexity'));
        $this->assertSame(15, $config->getRule('max_classes_per_namespace'));
        $this->assertSame(50, $config->getRule('max_layer_dominance_percentage'));
        $this->assertSame(0, $config->getRule('max_todo_fixme_comments'));
        $this->assertSame(0, $config->getRule('boolean_methods_without_prefix'));
    }

    public function testFromFile(): void
    {
        $path = __DIR__.'/../_fixture/code-quality.json';
        $config = QualityConfig::fromFile($path);

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

        $this->assertSame(30, $config->getRule('max_method_length'));
    }

    public function testResolveFindsSodaJsonBeforeCodeQualityJson(): void
    {
        $dir = sys_get_temp_dir().'/soda-resolve-'.uniqid();
        mkdir($dir, 0700, true);
        $sodaPath = $dir.'/soda.json';
        $codeQualityPath = $dir.'/code-quality.json';
        file_put_contents($sodaPath, '{"rules":{"structural":{"max_method_length":90},"complexity":{},"breathing":{}}}');
        file_put_contents($codeQualityPath, '{"rules":{"structural":{"max_method_length":75},"complexity":{},"breathing":{}}}');

        try {
            $config = ConfigResolver::resolveConfig([$dir.'/dummy.php']);
            $this->assertSame(90, $config->getRule('max_method_length'));
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
            $this->assertSame(100, $config->getRule('max_method_length'));
        } finally {
            rmdir($noConfigDir);
        }
    }

    public function testRuleWithZeroDisablesRule(): void
    {
        $dir = sys_get_temp_dir().'/soda-disable-'.uniqid();
        mkdir($dir, 0700, true);
        $path = $dir.'/soda.json';
        file_put_contents($path, '{"rules":{"structural":{},"complexity":{"max_control_nesting":0},"breathing":{"min_code_breathing_score":0}}}');

        try {
            $config = QualityConfig::fromFile($path);
            $this->assertSame(0, $config->getRule('max_control_nesting'));
            $this->assertSame(0, $config->getRule('min_code_breathing_score'));
        } finally {
            unlink($path);
            rmdir($dir);
        }
    }

    public function testNullRuleValueDisablesRule(): void
    {
        $dir = sys_get_temp_dir().'/soda-null-rule-'.uniqid();
        mkdir($dir, 0700, true);
        $path = $dir.'/soda.json';
        $payload = [
            'rules' => [
                'structural' => [
                    'max_method_length' => null,
                ],
                'complexity' => [],
                'breathing'  => [],
                'naming'     => [],
            ],
        ];
        file_put_contents($path, json_encode($payload, JSON_THROW_ON_ERROR));

        try {
            $config = QualityConfig::fromFile($path);
            $this->assertContains('max_method_length', $config->disabledRuleIds);
            $this->assertFalse($config->isRuleEnabled('max_method_length'));
            $this->assertTrue($config->isRuleEnabled('max_class_length'));
        } finally {
            unlink($path);
            rmdir($dir);
        }
    }

    public function testFromFileUsesDefaultsForMissingSections(): void
    {
        $dir = sys_get_temp_dir().'/soda-partial-'.uniqid();
        mkdir($dir, 0700, true);
        $path = $dir.'/soda.json';
        file_put_contents($path, '{"rules":{"structural":{"max_method_length":50},"complexity":{},"breathing":{}}}');

        try {
            $config = QualityConfig::fromFile($path);
            $this->assertSame(50, $config->getRule('max_method_length'));
            $this->assertSame(500, $config->getRule('max_class_length'));
        } finally {
            unlink($path);
            rmdir($dir);
        }
    }

    public function testFromFileLoadsGenericRuleExceptions(): void
    {
        $dir = sys_get_temp_dir().'/soda-bool-prefix-'.uniqid();
        mkdir($dir, 0700, true);
        $path = $dir.'/soda.json';
        file_put_contents($path, json_encode([
            'rules' => [
                'structural' => [],
                'complexity' => [],
                'breathing'  => [],
                'naming'     => [
                    'boolean_methods_without_prefix' => [
                        'threshold'  => 0,
                        'exceptions' => [
                            'methods' => ['runningUnitTests', 'App\Application::runningUnitTests'],
                            'classes' => ['App\Application'],
                            'files'   => ['/tmp/demo.php'],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        try {
            $config = QualityConfig::fromFile($path);
            $this->assertSame(
                [
                    'files'   => ['/tmp/demo.php'],
                    'classes' => ['App\Application'],
                    'methods' => ['runningUnitTests', 'App\Application::runningUnitTests'],
                ],
                $config->ruleExceptions('boolean_methods_without_prefix'),
            );
        } finally {
            unlink($path);
            rmdir($dir);
        }
    }

    public function testFromFileLoadsRuleOptions(): void
    {
        $dir = sys_get_temp_dir().'/soda-layer-mixing-'.uniqid();
        mkdir($dir, 0700, true);
        $path = $dir.'/soda.json';
        file_put_contents($path, json_encode([
            'rules' => [
                'structural' => [
                    'max_layer_dominance_percentage' => [
                        'threshold' => 60,
                        'min_files' => 6,
                    ],
                ],
                'complexity' => [],
                'breathing'  => [],
                'naming'     => [],
            ],
        ], JSON_THROW_ON_ERROR));

        try {
            $config = QualityConfig::fromFile($path);
            $this->assertSame(60, $config->getRule('max_layer_dominance_percentage'));
            $this->assertSame(['min_files' => 6], $config->ruleOptions('max_layer_dominance_percentage'));
        } finally {
            unlink($path);
            rmdir($dir);
        }
    }

    public function testFromFileSupportsLegacyBooleanMethodExceptionsKey(): void
    {
        $dir = sys_get_temp_dir().'/soda-bool-prefix-legacy-'.uniqid();
        mkdir($dir, 0700, true);
        $path = $dir.'/soda.json';
        file_put_contents($path, json_encode([
            'rules' => [
                'structural' => [],
                'complexity' => [],
                'breathing'  => [],
                'naming'     => [
                    'boolean_methods_without_prefix'     => 0,
                    'boolean_method_prefix_exceptions'   => ['runningUnitTests'],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        try {
            $config = QualityConfig::fromFile($path);
            $this->assertSame(
                [
                    'files'   => [],
                    'classes' => [],
                    'methods' => ['runningUnitTests'],
                ],
                $config->ruleExceptions('boolean_methods_without_prefix'),
            );
        } finally {
            unlink($path);
            rmdir($dir);
        }
    }
}
