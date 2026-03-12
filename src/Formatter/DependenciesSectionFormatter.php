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
final readonly class DependenciesSectionFormatter
{
    public function format(Result $result): string
    {
        $structure = $result->structure();
        if ($structure === null) {
            return '';
        }

        return $this->formatDependencies($structure);
    }

    private function formatDependencies(Metrics $structure): string
    {
        $global = $structure->globalAccesses();
        $attrs = $structure->attributeAccesses();
        $calls = $structure->methodCalls();

        return sprintf(
            <<<'EOT'
Dependencies
  Global Accesses                                     %20s
    Global Constants                                  %20s (%.2f%%)
    Global Variables                                  %20s (%.2f%%)
    Super-Global Variables                             %20s (%.2f%%)
  Attribute Accesses                                %20s
    Non-Static                                        %20s (%.2f%%)
    Static                                            %20s (%.2f%%)
  Method Calls                                     %20s
    Non-Static                                        %20s (%.2f%%)
    Static                                            %20s (%.2f%%)
EOT,
            number_format($global),
            number_format($structure->globalConstantAccesses()),
            $global > 0 ? ((float) $structure->globalConstantAccesses() / (float) $global) * 100.0 : 0.0,
            number_format($structure->globalVariableAccesses()),
            $global > 0 ? ((float) $structure->globalVariableAccesses() / (float) $global) * 100.0 : 0.0,
            number_format($structure->superGlobalVariableAccesses()),
            $global > 0 ? ((float) $structure->superGlobalVariableAccesses() / (float) $global) * 100.0 : 0.0,
            number_format($attrs),
            number_format($structure->nonStaticAttributeAccesses()),
            $attrs > 0 ? ((float) $structure->nonStaticAttributeAccesses() / (float) $attrs) * 100.0 : 0.0,
            number_format($structure->staticAttributeAccesses()),
            $attrs > 0 ? ((float) $structure->staticAttributeAccesses() / (float) $attrs) * 100.0 : 0.0,
            number_format($calls),
            number_format($structure->nonStaticMethodCalls()),
            $calls > 0 ? ((float) $structure->nonStaticMethodCalls() / (float) $calls) * 100.0 : 0.0,
            number_format($structure->staticMethodCalls()),
            $calls > 0 ? ((float) $structure->staticMethodCalls() / (float) $calls) * 100.0 : 0.0,
        )."\n";
    }
}
