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
final readonly class DependenciesFormatter
{
    use FormatHelpers;

    public function format(Result $result): string
    {
        $structure = $result->structure();
        if ($structure === null) {
            return '';
        }

        return $this->formatSection($structure);
    }

    private function formatSection(Metrics $structure): string
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
            self::pct($structure->globalConstantAccesses(), $global),
            number_format($structure->globalVariableAccesses()),
            self::pct($structure->globalVariableAccesses(), $global),
            number_format($structure->superGlobalVariableAccesses()),
            self::pct($structure->superGlobalVariableAccesses(), $global),
            number_format($attrs),
            number_format($structure->nonStaticAttributeAccesses()),
            self::pct($structure->nonStaticAttributeAccesses(), $attrs),
            number_format($structure->staticAttributeAccesses()),
            self::pct($structure->staticAttributeAccesses(), $attrs),
            number_format($calls),
            number_format($structure->nonStaticMethodCalls()),
            self::pct($structure->nonStaticMethodCalls(), $calls),
            number_format($structure->staticMethodCalls()),
            self::pct($structure->staticMethodCalls(), $calls),
        )."\n";
    }
}
