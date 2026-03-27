<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Commands;

use Bunnivo\Soda\Application;
use Bunnivo\Soda\Quality\QualityConfig;
use Bunnivo\Soda\Quality\Report\RuleMetadata;
use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Console\OutputStyle;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(InitCommand::class)]
#[Small]
final class InitCommandTest extends TestCase
{
    public function testInitCreatesFileWithAllPossibleRules(): void
    {
        $dir = sys_get_temp_dir().'/soda-init-test-'.uniqid();
        mkdir($dir, 0700, true);
        $path = $dir.'/soda.php';

        $cwd = getcwd();
        chdir($dir);

        try {
            $container = new Application();
            $artisan = new ConsoleApplication($container, new Dispatcher($container), '8.0');
            $artisan->setAutoExit(false);
            $artisan->add(new InitCommand());

            $input = new ArrayInput(['command' => 'init']);
            $output = new BufferedOutput();

            $exitCode = $artisan->run($input, new OutputStyle($input, $output));

            $this->assertSame(0, $exitCode);
            $this->assertFileExists($path);

            $content = file_get_contents($path);
            $this->assertNotFalse($content);
            $this->assertStringContainsString('extends SodaConfigurator', $content);
            $this->assertStringContainsString('->maxMethodLength(', $content);

            $config = QualityConfig::fromPhpConfiguratorFile($path);

            $this->assertSame(15, $config->getRule('max_classes_per_namespace'));
            $this->assertSame(50, $config->getRule('max_layer_dominance_percentage'));
            $this->assertSame(['min_files' => 4], $config->ruleOptions('max_layer_dominance_percentage'));

            $createdRules = array_keys($config->rules);
            $expectedKeys = RuleMetadata::default()->ruleKeys();

            foreach ($expectedKeys as $key) {
                $this->assertContains($key, $createdRules, 'Init config missing rule: '.$key);
            }

            foreach ($createdRules as $key) {
                $this->assertContains($key, $expectedKeys, 'Init config has unknown rule: '.$key);
            }
        } finally {
            chdir($cwd);
            if (is_file($path)) {
                unlink($path);
            }

            rmdir($dir);
        }
    }

    public function testInitFailsWhenSodaPhpAlreadyExists(): void
    {
        $dir = sys_get_temp_dir().'/soda-init-fail-'.uniqid();
        mkdir($dir, 0700, true);
        file_put_contents($dir.'/soda.php', "<?php\nreturn static function (): void {};\n");

        $cwd = getcwd();
        chdir($dir);

        try {
            $container = new Application();
            $artisan = new ConsoleApplication($container, new Dispatcher($container), '8.0');
            $artisan->setAutoExit(false);
            $artisan->add(new InitCommand());

            $input = new ArrayInput(['command' => 'init']);
            $output = new BufferedOutput();

            $exitCode = $artisan->run($input, new OutputStyle($input, $output));

            $this->assertSame(1, $exitCode);
        } finally {
            chdir($cwd);
            unlink($dir.'/soda.php');
            rmdir($dir);
        }
    }
}
