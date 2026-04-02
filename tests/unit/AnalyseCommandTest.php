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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class AnalyseCommandTest extends TestCase
{
    public function testReportJsonCreatesValidJsonFile(): void
    {
        $reportPath = sys_get_temp_dir().'/soda-analyse-test-'.uniqid().'.json';

        $container = new Application();
        $artisan = new ConsoleApplication($container, new Dispatcher($container), '8.0');
        $artisan->setAutoExit(false);
        $artisan->add(new AnalyseCommand());

        $input = new ArrayInput([
            'command'       => 'analyse',
            'path'          => [__DIR__.'/../_fixture'],
            '--report-json' => $reportPath,
        ]);
        $output = new BufferedOutput();

        $artisan->run($input, new OutputStyle($input, $output));

        $this->assertFileExists($reportPath);

        $json = file_get_contents($reportPath);
        unlink($reportPath);

        $data = json_decode($json, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('directories', $data);
        $this->assertArrayHasKey('files', $data);
        $this->assertArrayHasKey('loc', $data);
        $this->assertArrayHasKey('complexity', $data);
    }

    public function testReportJsonNotWrittenWhenOptionOmitted(): void
    {
        $reportPath = sys_get_temp_dir().'/soda-analyse-nonexistent-'.uniqid().'.json';

        $container = new Application();
        $artisan = new ConsoleApplication($container, new Dispatcher($container), '8.0');
        $artisan->setAutoExit(false);
        $artisan->add(new AnalyseCommand());

        $input = new ArrayInput([
            'command' => 'analyse',
            'path'    => [__DIR__.'/../_fixture'],
        ]);
        $output = new BufferedOutput();

        $artisan->run($input, new OutputStyle($input, $output));

        $this->assertFileDoesNotExist($reportPath);
    }
}
