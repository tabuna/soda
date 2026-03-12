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
        $arr = $structure->toArray();
        $global = $arr['globalAccesses'];
        $attrs = $arr['attributeAccesses'];
        $calls = $arr['methodCalls'];

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
            number_format($arr['globalConstantAccesses']),
            self::pct($arr['globalConstantAccesses'], $global),
            number_format($arr['globalVariableAccesses']),
            self::pct($arr['globalVariableAccesses'], $global),
            number_format($arr['superGlobalVariableAccesses']),
            self::pct($arr['superGlobalVariableAccesses'], $global),
            number_format($attrs),
            number_format($arr['nonStaticAttributeAccesses']),
            self::pct($arr['nonStaticAttributeAccesses'], $attrs),
            number_format($arr['staticAttributeAccesses']),
            self::pct($arr['staticAttributeAccesses'], $attrs),
            number_format($calls),
            number_format($arr['nonStaticMethodCalls']),
            self::pct($arr['nonStaticMethodCalls'], $calls),
            number_format($arr['staticMethodCalls']),
            self::pct($arr['staticMethodCalls'], $calls),
        )."\n";
    }
}
