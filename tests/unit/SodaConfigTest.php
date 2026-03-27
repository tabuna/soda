<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Quality\ConfigException;
use Bunnivo\Soda\Quality\QualityConfig;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(SodaConfig::class)]
#[Small]
final class SodaConfigTest extends TestCase
{
    public function testBasicFluentScenario(): void
    {
        $c = new SodaConfig;
        $c->structural()
            ->maxMethodLength(100)
            ->maxClassLength(800)
            ->maxArguments(3);
        $c->complexity()
            ->maxCyclomaticComplexity(15)
            ->maxControlNesting(3);
        $c->breathing()
            ->minCodeBreathingScore(25);

        $data = $c->toArray();

        $this->assertSame(100, $data['rules']['structural']['max_method_length']);
        $this->assertSame(800, $data['rules']['structural']['max_class_length']);
        $this->assertSame(3, $data['rules']['structural']['max_arguments']);
        $this->assertSame(15, $data['rules']['complexity']['max_cyclomatic_complexity']);
        $this->assertSame(3, $data['rules']['complexity']['max_control_nesting']);
        $this->assertSame(25, $data['rules']['breathing']['min_code_breathing_score']);
    }

    public function testOverrideLastWinsInArray(): void
    {
        $c = new SodaConfig;
        $c->structural()->maxMethodLength(80)->maxMethodLength(120);

        $this->assertSame(120, $c->toArray()['rules']['structural']['max_method_length']);
    }

    public function testInvalidMaxMethodLengthThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new SodaConfig)->structural()->maxMethodLength(0);
    }

    public function testDisableRuleProducesNullInSection(): void
    {
        $c = new SodaConfig;
        $c->structural()->maxMethodLength(50);
        $c->disableRule('max_method_length');

        $this->assertNull($c->toArray()['rules']['structural']['max_method_length']);
    }

    public function testFromPhpConfiguratorFile(): void
    {
        $path = sys_get_temp_dir().'/soda-php-cfg-'.uniqid().'.php';
        file_put_contents($path, <<<'PHP'
<?php

declare(strict_types=1);

use Bunnivo\Soda\Config\SodaConfig;

return static function (SodaConfig $config): void {
    $config->structural()->maxMethodLength(77);
};
PHP);

        try {
            $qc = QualityConfig::fromPhpConfiguratorFile($path);
            $this->assertSame(77, $qc->getRule('max_method_length'));
        } finally {
            unlink($path);
        }
    }

    public function testFromPhpConfiguratorFileRejectsNonCallable(): void
    {
        $path = sys_get_temp_dir().'/soda-php-bad-'.uniqid().'.php';
        file_put_contents($path, "<?php\nreturn [];\n");

        try {
            $this->expectException(ConfigException::class);
            QualityConfig::fromPhpConfiguratorFile($path);
        } finally {
            unlink($path);
        }
    }
}
