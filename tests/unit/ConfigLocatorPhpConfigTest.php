<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Tests;

use Bunnivo\Soda\Quality\Config\ConfigLocator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigLocator::class)]
#[Small]
final class ConfigLocatorPhpConfigTest extends TestCase
{
    public function testLocatesPhpConfigBesideSodaPhp(): void
    {
        $root = sys_get_temp_dir().'/soda-locate-php-'.uniqid();
        mkdir($root.'/config', 0700, true);
        mkdir($root.'/src', 0700, true);
        touch($root.'/soda.php');
        file_put_contents($root.'/config/soda.php', "<?php return ['rules' => []];\n");
        $file = $root.'/src/Foo.php';
        touch($file);

        try {
            $locator = new ConfigLocator;
            $phpPath = $locator->locatePhpConfig([$file], $root.'/soda.php');

            $this->assertSame($root.'/config/soda.php', $phpPath);
        } finally {
            unlink($file);
            rmdir($root.'/src');
            unlink($root.'/config/soda.php');
            rmdir($root.'/config');
            unlink($root.'/soda.php');
            rmdir($root);
        }
    }

    public function testLocatesPhpConfigUpwardFromScannedFile(): void
    {
        $root = sys_get_temp_dir().'/soda-locate-up-'.uniqid();
        mkdir($root.'/deep/nested', 0700, true);
        mkdir($root.'/config', 0700, true);
        file_put_contents($root.'/config/soda.php', "<?php return ['rules' => []];\n");
        $file = $root.'/deep/nested/X.php';
        touch($file);

        try {
            $locator = new ConfigLocator;
            $phpPath = $locator->locatePhpConfig([$file]);

            $this->assertSame($root.'/config/soda.php', $phpPath);
        } finally {
            unlink($file);
            rmdir($root.'/deep/nested');
            rmdir($root.'/deep');
            unlink($root.'/config/soda.php');
            rmdir($root.'/config');
            rmdir($root);
        }
    }
}
