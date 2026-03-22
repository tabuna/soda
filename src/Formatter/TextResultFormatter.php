<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Formatter;

use function assert;

use Bunnivo\Soda\Breathing\BreathingMetrics;
use Bunnivo\Soda\Result;

use function number_format;
use function sprintf;

final readonly class TextResultFormatter
{
    public function __construct(
        private SizeFormatter $sizeFormatter = new SizeFormatter(),
        private DependenciesFormatter $depsFormatter = new DependenciesFormatter(),
        private StructureFormatter $structureFormatter = new StructureFormatter(),
    ) {}

    /**
     * @psalm-return non-empty-string
     */
    public function format(Result $result): string
    {
        $buf = collect([
            $this->formatHeader($result),
            $this->sizeFormatter->format($result),
            $this->formatComplexity($result),
            $this->depsFormatter->format($result),
            $this->structureFormatter->format($result),
        ])
            ->implode('');

        assert($buf !== '');

        return $buf;
    }

    private function formatHeader(Result $result): string
    {
        $s = $result->loc()->stats();

        return sprintf(
            <<<'EOT'
Directories:                                         %20s
Files:                                               %20s

EOT,
            number_format($s['directories']),
            number_format($s['files']),
        );
    }

    private function formatComplexity(Result $result): string
    {
        $c = $result->complexity();
        $m = $c->methods();

        $classStats = $c->classes();

        $buf = sprintf(
            <<<'EOT'
Cyclomatic Complexity
  Average Complexity per LLOC                         %20.2f
  Average Complexity per Class                        %20.2f
    Minimum Class Complexity                          %20.2f
    Maximum Class Complexity                          %20.2f
  Average Complexity per Method                       %20.2f
    Minimum Method Complexity                         %20.2f
    Maximum Method Complexity                         %20.2f
EOT,
            $c->averagePerLloc(),
            $classStats['average'],
            $classStats['lowest'],
            $classStats['highest'],
            $m['average'],
            $m['lowest'],
            $m['highest'],
        )."\n";

        $breathing = $result->breathing();

        if ($breathing instanceof BreathingMetrics) {
            $buf .= sprintf(
                <<<'EOT'

Code Breathing Score (CBS)
  Weighted Cognitive Density (WCD)                     %20.2f
  Logical Complexity Factor (LCF)                      %20.2f
  Visual Breathing Index (VBI)                        %20.2f
  Identifier Readability Score (IRS)                 %20.2f
  Code Oxygen Level (COL)                              %20.2f
  Code Breathing Score (CBS)                           %20.2f
EOT,
                $breathing->wcd(),
                $breathing->lcf(),
                $breathing->vbi(),
                $breathing->irs(),
                $breathing->col(),
                $breathing->cbs(),
            )."\n";
        }

        return $buf;
    }
}
