<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Commands;

use Bunnivo\Soda\Formatter\JsonResultFormatter;
use Bunnivo\Soda\Formatter\TextResultFormatter;
use Bunnivo\Soda\ProjectMetrics;
use Bunnivo\Soda\Result;

use function file_put_contents;

use Illuminate\Console\Command;

use function json_encode;

use SebastianBergmann\FileIterator\Facade;

final class AnalyseCommand extends Command
{
    protected $signature = 'analyse
        {path?* : Directory or directories to analyse}
        {--suffix= : Include files with names ending in suffix (default: .php)}
        {--exclude=* : Exclude files with path in their path}
        {--debug : Print debugging information}
        {--report-json= : Write analysis report to JSON file}
    ';

    protected $description = 'Analyse PHP project size and collect metrics';

    public function handle(): int
    {
        /** @var list<non-empty-string> $directories */
        $directories = (array) $this->argument('path');

        if ($directories === []) {
            $this->error('No directory specified');

            return self::FAILURE;
        }

        $suffixOpt = $this->option('suffix');
        $suffixes = $suffixOpt === null ? ['.php'] : array_merge(['.php'], (array) $suffixOpt);
        /** @var list<non-empty-string> $suffixes */
        $exclude = (array) ($this->option('exclude') ?? []);
        /** @var list<non-empty-string> $exclude */
        $files = (new Facade)->getFilesAsArray($directories, $suffixes, '', $exclude);

        if ($files === []) {
            $this->error('No files found to scan');

            return self::FAILURE;
        }

        $result = (new ProjectMetrics)->analyse($files, (bool) $this->option('debug'));

        $this->line((new TextResultFormatter)->format($result));
        $this->writeReportJson($result);

        return self::SUCCESS;
    }

    private function writeReportJson(Result $result): void
    {
        $reportJson = $this->option('report-json');

        if (! is_string($reportJson) || $reportJson === '') {
            return;
        }

        $data = (new JsonResultFormatter)->format($result);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        file_put_contents($reportJson, $json);
    }
}
