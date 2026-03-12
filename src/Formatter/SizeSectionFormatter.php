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

namespace Bunnivo\Soda\Formatter;

use Bunnivo\Soda\Result;
use Bunnivo\Soda\Structure\Metrics;

use function number_format;
use function sprintf;

/**
 * @internal
 */
final readonly class SizeSectionFormatter
{
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

        if ($structure !== null && $s['logicalLinesOfCode'] > 0) {
            $buffer .= $this->formatStructureBreakdown($s['logicalLinesOfCode'], $structure);
        }

        return $buffer."\n";
    }

    private function formatStructureBreakdown(int $lloc, Metrics $structure): string
    {
        $llocClasses = $structure->llocClasses();
        $llocFunctions = $structure->llocFunctions();
        $llocGlobal = $structure->llocGlobal();

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
            $lloc > 0 ? ((float) $llocClasses / (float) $lloc) * 100.0 : 0.0,
            number_format($structure->classLlocAvg()),
            number_format($structure->classLlocMin()),
            number_format($structure->classLlocMax()),
            number_format($structure->methodLlocAvg()),
            number_format($structure->methodLlocMin()),
            number_format($structure->methodLlocMax()),
            number_format($structure->averageMethodsPerClass()),
            number_format($structure->minimumMethodsPerClass()),
            number_format($structure->maximumMethodsPerClass()),
            number_format($llocFunctions),
            $lloc > 0 ? ((float) $llocFunctions / (float) $lloc) * 100.0 : 0.0,
            number_format($structure->averageFunctionLength()),
            number_format($llocGlobal),
            $lloc > 0 ? ((float) $llocGlobal / (float) $lloc) * 100.0 : 0.0,
        );
    }
}
