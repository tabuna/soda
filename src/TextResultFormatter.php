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

namespace Bunnivo\Soda;

use function assert;

use Bunnivo\Soda\Formatter\DependenciesSectionFormatter;
use Bunnivo\Soda\Formatter\SizeSectionFormatter;
use Bunnivo\Soda\Formatter\StructureSectionFormatter;

use function number_format;
use function sprintf;

final readonly class TextResultFormatter
{
    public function __construct(
        private SizeSectionFormatter $sizeFormatter = new SizeSectionFormatter(),
        private DependenciesSectionFormatter $depsFormatter = new DependenciesSectionFormatter(),
        private StructureSectionFormatter $structureFormatter = new StructureSectionFormatter(),
    ) {}

    /**
     * @psalm-return non-empty-string
     */
    public function format(Result $result): string
    {
        $buffer = $this->formatHeader($result);
        $buffer .= $this->sizeFormatter->format($result);
        $buffer .= $this->formatComplexity($result);
        $buffer .= $this->depsFormatter->format($result);
        $buffer .= $this->structureFormatter->format($result);

        assert($buffer !== '');

        return $buffer;
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
        $classCcn = $c->classes();

        return sprintf(
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
            $classCcn['average'],
            $classCcn['lowest'],
            $classCcn['highest'],
            $m['average'],
            $m['lowest'],
            $m['highest'],
        )."\n";
    }
}
