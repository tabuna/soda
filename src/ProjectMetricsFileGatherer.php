<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use function assert;

use Bunnivo\Soda\Breathing\BreathingMetrics;
use Bunnivo\Soda\Exception\ParserException;

use function dirname;
use function is_string;

use SebastianBergmann\Complexity\ComplexityCollection;
use SebastianBergmann\LinesOfCode\LinesOfCode;

/**
 * @internal
 */
final class ProjectMetricsFileGatherer
{
    /**
     * @psalm-param list<non-empty-string> $files
     *
     * @psalm-return array{
     *     files: list<non-empty-string>,
     *     errors: list<non-empty-string>,
     *     dirs: list<string>,
     *     complexity: ComplexityCollection,
     *     loc: LinesOfCode,
     *     structureResults: list<array<string, mixed>>,
     *     breathingList: list<BreathingMetrics>
     * }
     */
    public static function gather(array $files, bool $debug): array
    {
        $errors = [];
        $dirs = [];
        $complexity = ComplexityCollection::fromList();
        /** @psalm-suppress MissingThrowsDocblock */
        $loc = new LinesOfCode(0, 0, 0, 0);
        $structureResults = [];
        $breathingList = [];

        foreach ($files as $file) {
            if ($debug) {
                echo $file.PHP_EOL;
            }

            $dirs[] = dirname($file);

            try {
                $result = (new FileAnalyser)->analyse($file);
                $complexity = $complexity->mergeWith($result['complexity']);
                $loc = $loc->plus($result['linesOfCode']);
                $structureResults[] = $result['structure'];
                $breathingList[] = $result['breathing'];
            } catch (ParserException $e) {
                $message = $e->getMessage();
                assert(is_string($message) && ($message !== '' && $message !== '0'));
                $errors[] = $message;
            }
        }

        return [
            'files'            => $files,
            'errors'           => $errors,
            'dirs'             => $dirs,
            'complexity'       => $complexity,
            'loc'              => $loc,
            'structureResults' => $structureResults,
            'breathingList'    => $breathingList,
        ];
    }
}
