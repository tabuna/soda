<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\ParserException;
use Bunnivo\Soda\ProjectMetrics;

final readonly class QualityAnalyser implements QualityAnalysisContract
{
    public function __construct(
        private PhpFileQualityExtractor $fileExtractor = new PhpFileQualityExtractor(),
    ) {}

    /**
     * @psalm-param list<non-empty-string> $files
     * @psalm-param non-empty-string|null $configPath
     *
     * @throws ConfigException
     */
    public function analyse(array $files, bool $debug, ?string $configPath = null): QualityResult
    {
        $metrics = new ProjectMetrics;
        $result = $metrics->analyse($files, $debug);

        $accumulator = new QualityAnalyserPerFileMetricsAccumulator;

        foreach ($files as $file) {
            try {
                $accumulator->mergeFile($file, $this->fileExtractor->extract($file));
            } catch (ParserException|\PhpParser\Error) {
                continue;
            }
        }

        $engine = QualityAnalyserConfigurationSession::engineForFiles($files, $configPath);

        return $engine->evaluate($result, $accumulator->evaluateInput());
    }
}
