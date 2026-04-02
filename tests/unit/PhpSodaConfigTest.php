<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Tests;

use Bunnivo\Soda\Quality\Config\PhpSodaConfig;
use Bunnivo\Soda\Quality\ConfigException;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use PHPUnit\Framework\TestCase;

final class PhpSodaConfigTest extends TestCase
{
    public function testReturnsEmptyWhenPathMissing(): void
    {
        $this->assertSame([], PhpSodaConfig::checkersFromPath(null));
        $this->assertSame([], PhpSodaConfig::checkersFromPath(''));
        $this->assertSame([], PhpSodaConfig::checkersFromPath('/nonexistent/soda-php-config.php'));
    }

    public function testThrowsWhenFileDoesNotReturnArray(): void
    {
        $path = sys_get_temp_dir().'/soda-php-bad-'.uniqid().'.php';
        file_put_contents($path, "<?php\nreturn 1;\n");

        try {
            $this->expectException(ConfigException::class);
            PhpSodaConfig::checkersFromPath($path);
        } finally {
            unlink($path);
        }
    }

    public function testThrowsWhenRulesIsNotArray(): void
    {
        $path = sys_get_temp_dir().'/soda-php-bad-rules-'.uniqid().'.php';
        file_put_contents($path, "<?php\nreturn ['rules' => 'nope'];\n");

        try {
            $this->expectException(ConfigException::class);
            PhpSodaConfig::checkersFromPath($path);
        } finally {
            unlink($path);
        }
    }

    public function testLoadsRuleCheckerClasses(): void
    {
        $path = sys_get_temp_dir().'/soda-php-ok-'.uniqid().'.php';
        $content = <<<'PHP'
<?php
declare(strict_types=1);

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Illuminate\Support\Collection;

final class SodaPhpConfigTestTempRule implements RuleChecker
{
    #[\Override]
    public function check(EvaluationContext $context): Collection
    {
        return collect([]);
    }
}

return [
    'rules' => [
        SodaPhpConfigTestTempRule::class,
    ],
];
PHP;
        file_put_contents($path, $content);

        try {
            $checkers = PhpSodaConfig::checkersFromPath($path);
            $this->assertCount(1, $checkers);
            $this->assertInstanceOf(RuleChecker::class, $checkers[0]);
        } finally {
            unlink($path);
        }
    }
}
