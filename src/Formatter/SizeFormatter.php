<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Formatter;

use Bunnivo\Soda\Result;
use Bunnivo\Soda\Structure\Metrics;

use function number_format;
use function sprintf;

/**
 * @internal
 */
final readonly class SizeFormatter
{
    use FormatHelpers;

    public function format(Result $result): string
    {
        $s = $result->loc()->stats();
        $pct = $result->loc()->percentages();
        $structure = $result->structure();

        $buffer = sprintf(
            <<<'EOT'
Size
  Lines of Code (LOC)                                %20s
  Comment Lines of Code (CLOC)                       %20s (%.2f%%)
  Non-Comment Lines of Code (NCLOC)                  %20s (%.2f%%)
  Logical Lines of Code (LLOC)                       %20s (%.2f%%)
EOT,
            number_format($s['linesOfCode']),
            number_format($s['commentLinesOfCode']),
            $pct['comment'],
            number_format($s['nonCommentLinesOfCode']),
            $pct['nonComment'],
            number_format($s['logicalLinesOfCode']),
            $pct['logical'],
        );

        if ($structure === null || $s['logicalLinesOfCode'] <= 0) {
            return $buffer."\n";
        }

        return $buffer
            .$this->formatBreakdown($s['logicalLinesOfCode'], $structure)
            ."\n";
    }

    private function formatBreakdown(int $lloc, Metrics $structure): string
    {
        $arr = $structure->toArray();
        $llocClasses = $arr['llocClasses'];
        $llocFunctions = $arr['llocFunctions'];
        $llocGlobal = $arr['llocGlobal'];

        return sprintf(
            <<<'EOT'

    Classes                                           %20s (%.2f%%)
      Average Class Length                            %20s
        Minimum Class Length                          %20s
        Maximum Class Length                          %20s
      Average Method Length                           %20s
        Minimum Method Length                         %20s
        Maximum Method Length                         %20s
      Average Methods Per Class                       %20s
        Minimum Methods Per Class                     %20s
        Maximum Methods Per Class                     %20s
    Functions                                         %20s (%.2f%%)
      Average Function Length                         %20s
    Not in classes or functions                       %20s (%.2f%%)
EOT,
            number_format($llocClasses),
            self::pct($llocClasses, $lloc),
            number_format($arr['classLlocAvg']),
            number_format($arr['classLlocMin']),
            number_format($arr['classLlocMax']),
            number_format($arr['methodLlocAvg']),
            number_format($arr['methodLlocMin']),
            number_format($arr['methodLlocMax']),
            number_format($arr['averageMethodsPerClass']),
            number_format($arr['minimumMethodsPerClass']),
            number_format($arr['maximumMethodsPerClass']),
            number_format($llocFunctions),
            self::pct($llocFunctions, $lloc),
            number_format($arr['averageFunctionLength']),
            number_format($llocGlobal),
            self::pct($llocGlobal, $lloc),
        );
    }
}
