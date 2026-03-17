<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Bunnivo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda\Commands;

use Bunnivo\Soda\Application;
use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Console\OutputStyle;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(QualityCommand::class)]
#[Small]
final class QualityCommandTest extends TestCase
{
    /**
     * @group enum-workaround
     * REMOVE_WHEN sebastian/complexity adds Enum support (see EnumAwareComplexityVisitorTest)
     */
    #[Group('enum-workaround')]
    public function testQualityRunsOnFixtureWithEnumWithoutCrashing(): void
    {
        $container = new Application();
        $artisan = new ConsoleApplication($container, new Dispatcher($container), '8.0');
        $artisan->setAutoExit(false);
        $artisan->add(new QualityCommand());

        $input = new ArrayInput([
            'command' => 'quality',
            'path'    => [__DIR__.'/../quality-fixture'],
        ]);
        $output = new BufferedOutput();

        $exitCode = $artisan->run($input, new OutputStyle($input, $output));

        $this->assertContains($exitCode, [0, 1], 'Не должно падать с AssertionError на Enum');
    }

    public function testReportJsonIncludesMetricsAndViolations(): void
    {
        $reportPath = sys_get_temp_dir().'/soda-quality-test-'.uniqid().'.json';

        $container = new Application();
        $artisan = new ConsoleApplication($container, new Dispatcher($container), '8.0');
        $artisan->setAutoExit(false);
        $artisan->add(new QualityCommand());

        $input = new ArrayInput([
            'command'       => 'quality',
            'path'          => [__DIR__.'/../quality-fixture'],
            '--report-json' => $reportPath,
        ]);
        $output = new BufferedOutput();

        $artisan->run($input, new OutputStyle($input, $output));

        $this->assertFileExists($reportPath);

        $json = file_get_contents($reportPath);
        unlink($reportPath);

        $data = json_decode($json, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('score', $data);
        $this->assertArrayHasKey('metrics', $data);
        $this->assertArrayHasKey('violations', $data);
        $this->assertArrayHasKey('directories', $data['metrics']);
        $this->assertArrayHasKey('loc', $data['metrics']);
    }
}
