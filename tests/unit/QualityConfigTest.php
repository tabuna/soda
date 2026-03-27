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

    public function testFromPhpFixture(): void
    {
        $path = __DIR__.'/../config-fixtures/explicit-soda.php';
        $config = QualityConfig::fromPhpConfiguratorFile($path);

        $this->assertSame(30, $config->getRule('max_method_length'));
        $this->assertSame(600, $config->getRule('max_class_length'));
    }

    public function testFromPhpConfiguratorThrowsWhenNotReadable(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Config file not readable');

        QualityConfig::fromPhpConfiguratorFile('/nonexistent/soda.php');
    }

    public function testResolveUsesExplicitPath(): void
    {
        $path = __DIR__.'/../config-fixtures/explicit-soda.php';
        $config = ConfigResolver::resolveConfig([__FILE__], $path);

        $this->assertSame(30, $config->getRule('max_method_length'));
    }

    public function testResolveFindsSodaPhp(): void
    {
        $dir = sys_get_temp_dir().'/soda-resolve-php-'.uniqid();
        mkdir($dir, 0700, true);
        $sodaPath = $dir.'/soda.php';
        file_put_contents($sodaPath, <<<'PHP'
<?php

declare(strict_types=1);

use Bunnivo\Soda\Config\SodaConfig;

return static function (SodaConfig $config): void {
    $config->structural()->maxMethodLength(90);
};
PHP
        );

        try {
            $config = ConfigResolver::resolveConfig([$dir.'/dummy.php']);
            $this->assertSame(90, $config->getRule('max_method_length'));
        } finally {
            unlink($sodaPath);
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
        $config = QualityConfig::fromRulesData([
            'rules' => [
                'structural' => [],
                'complexity' => [
                    'max_control_nesting' => 0,
                ],
                'breathing' => [
                    'min_code_breathing_score' => 0,
                ],
                'naming' => [],
            ],
        ]);

        $this->assertSame(0, $config->getRule('max_control_nesting'));
        $this->assertSame(0, $config->getRule('min_code_breathing_score'));
    }

    public function testNullRuleValueDisablesRule(): void
    {
        $config = QualityConfig::fromRulesData([
            'rules' => [
                'structural' => [
                    'max_method_length' => null,
                ],
                'complexity' => [],
                'breathing'  => [],
                'naming'     => [],
            ],
        ]);

        $this->assertContains('max_method_length', $config->disabledRuleIds);
        $this->assertFalse($config->isRuleEnabled('max_method_length'));
        $this->assertTrue($config->isRuleEnabled('max_class_length'));
    }

    public function testFromRulesDataUsesDefaultsForMissingSections(): void
    {
        $config = QualityConfig::fromRulesData([
            'rules' => [
                'structural' => [
                    'max_method_length' => 50,
                ],
                'complexity' => [],
                'breathing'  => [],
                'naming'     => [],
            ],
        ]);

        $this->assertSame(50, $config->getRule('max_method_length'));
        $this->assertSame(500, $config->getRule('max_class_length'));
    }

    public function testFromRulesDataLoadsGenericRuleExceptions(): void
    {
        $config = QualityConfig::fromRulesData([
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
        ]);

        $this->assertSame(
            [
                'files'   => ['/tmp/demo.php'],
                'classes' => ['App\Application'],
                'methods' => ['runningUnitTests', 'App\Application::runningUnitTests'],
            ],
            $config->ruleExceptions('boolean_methods_without_prefix'),
        );
    }

    public function testFromRulesDataLoadsRuleOptions(): void
    {
        $config = QualityConfig::fromRulesData([
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
        ]);

        $this->assertSame(60, $config->getRule('max_layer_dominance_percentage'));
        $this->assertSame(['min_files' => 6], $config->ruleOptions('max_layer_dominance_percentage'));
    }

    public function testFromRulesDataSupportsLegacyBooleanMethodExceptionsKey(): void
    {
        $config = QualityConfig::fromRulesData([
            'rules' => [
                'structural' => [],
                'complexity' => [],
                'breathing'  => [],
                'naming'     => [
                    'boolean_methods_without_prefix'   => 0,
                    'boolean_method_prefix_exceptions' => ['runningUnitTests'],
                ],
            ],
        ]);

        $this->assertSame(
            [
                'files'   => [],
                'classes' => [],
                'methods' => ['runningUnitTests'],
            ],
            $config->ruleExceptions('boolean_methods_without_prefix'),
        );
    }
}
