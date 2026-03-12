<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Commands;

use Bunnivo\Soda\JsonResultFormatter;
use Bunnivo\Soda\Quality\Config\ConfigResolver;
use Bunnivo\Soda\Quality\ConfigException;
use Bunnivo\Soda\Quality\QualityAnalyser;
use Bunnivo\Soda\Quality\QualityResult;
use Bunnivo\Soda\Quality\ReportFormatter;
use Bunnivo\Soda\Quality\RuleMetadata;

use function file_put_contents;

use Illuminate\Console\Command;

use function json_encode;

use SebastianBergmann\FileIterator\Facade;

final class QualityCommand extends Command
{
    protected $signature = 'quality
        {path?* : Directory or directories to analyse}
        {--suffix= : Include files with names ending in suffix (default: .php)}
        {--exclude=* : Exclude files with path in their path}
        {--debug : Print debugging information}
        {--config= : Path to soda.json}
        {--report-json= : Write quality report to JSON file}
    ';

    protected $description = 'Analyse code quality and check against configured thresholds';

    /**
     * @throws ConfigException
     */
    public function handle(): int
    {
        /** @var list<non-empty-string> $directories */
        $directories = (array) $this->argument('path');
        if ($directories === []) {
            $this->error('No directory specified');

            return self::FAILURE;
        }

        $files = $this->resolveFiles($directories);
        if ($files === []) {
            $this->error('No files found to scan');

            return self::FAILURE;
        }

        $analyser = new QualityAnalyser();
        $configPath = $this->resolveConfigPath();
        $result = $analyser->analyse($files, (bool) $this->option('debug'), $configPath);

        $this->line((new ReportFormatter(RuleMetadata::default()))->format($result));
        $this->writeReportJson($result);

        /** @throws ConfigException */
        $config = ConfigResolver::resolveConfig($files, $configPath);

        return $result->passes($config->minScore) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param list<non-empty-string> $directories
     *
     * @return list<non-empty-string>
     */
    private function resolveFiles(array $directories): array
    {
        $suffixOpt = $this->option('suffix');
        $suffixes = $suffixOpt === null ? ['.php'] : array_merge(['.php'], (array) $suffixOpt);
        /** @var list<non-empty-string> $suffixes */
        $exclude = (array) ($this->option('exclude') ?? []);
        /** @var list<non-empty-string> $exclude */

        return (new Facade)->getFilesAsArray($directories, $suffixes, '', $exclude);
    }

    /**
     * @return non-empty-string|null
     */
    private function resolveConfigPath(): ?string
    {
        $configOpt = $this->option('config');
        if (! is_string($configOpt) || $configOpt === '') {
            return null;
        }

        return $configOpt;
    }

    private function writeReportJson(QualityResult $result): void
    {
        $reportJson = $this->option('report-json');
        if (! is_string($reportJson) || $reportJson === '') {
            return;
        }

        $data = [
            'score'      => $result->score,
            'metrics'    => (new JsonResultFormatter)->format($result->metrics),
            'violations' => $result->violations->map(fn ($v) => $v->toArray())->all(),
        ];
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        file_put_contents($reportJson, $json);
    }
}
