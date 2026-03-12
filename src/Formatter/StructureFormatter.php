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
final readonly class StructureFormatter
{
    use FormatHelpers;

    public function format(Result $result): string
    {
        $structure = $result->structure();
        if ($structure === null) {
            return '';
        }

        $arr = $structure->toArray();
        $classes = $arr['classes'];
        $concrete = $arr['concreteClasses'];
        $methods = $arr['methods'];
        $functions = $arr['functions'];
        $constants = $arr['constants'];
        $classConstants = $arr['classConstants'];

        return collect([
            $this->formatHeader($structure, $classes, $concrete),
            $this->formatMethods($structure, $methods),
            $this->formatFunctions($structure, $functions),
            $this->formatConstants($structure, $constants, $classConstants),
        ])
            ->implode('')
            ."\n";
    }

    private function formatHeader(
        Metrics $structure,
        int $classes,
        int $concrete,
    ): string {
        $arr = $structure->toArray();

        return sprintf(
            <<<'EOT'
Structure
  Namespaces                                          %20s
  Interfaces                                          %20s
  Traits                                              %20s
  Classes                                             %20s
    Abstract Classes                                  %20s (%.2f%%)
    Concrete Classes                                  %20s (%.2f%%)
      Final Classes                                   %20s (%.2f%%)
      Non-Final Classes                               %20s (%.2f%%)
EOT,
            number_format($arr['namespaces']),
            number_format($arr['interfaces']),
            number_format($arr['traits']),
            number_format($classes),
            number_format($arr['abstractClasses']),
            self::pct($arr['abstractClasses'], $classes),
            number_format($concrete),
            self::pct($concrete, $classes),
            number_format($arr['finalClasses']),
            self::pct($arr['finalClasses'], $concrete),
            number_format($arr['nonFinalClasses']),
            self::pct($arr['nonFinalClasses'], $concrete),
        );
    }

    private function formatMethods(
        Metrics $structure,
        int $methods,
    ): string {
        $arr = $structure->toArray();

        return sprintf(
            <<<'EOT'
  Methods                                             %20s
    Scope
      Non-Static Methods                              %20s (%.2f%%)
      Static Methods                                  %20s (%.2f%%)
    Visibility
      Public Methods                                  %20s (%.2f%%)
      Protected Methods                               %20s (%.2f%%)
      Private Methods                                 %20s (%.2f%%)
EOT,
            number_format($methods),
            number_format($arr['nonStaticMethods']),
            self::pct($arr['nonStaticMethods'], $methods),
            number_format($arr['staticMethods']),
            self::pct($arr['staticMethods'], $methods),
            number_format($arr['publicMethods']),
            self::pct($arr['publicMethods'], $methods),
            number_format($arr['protectedMethods']),
            self::pct($arr['protectedMethods'], $methods),
            number_format($arr['privateMethods']),
            self::pct($arr['privateMethods'], $methods),
        );
    }

    private function formatFunctions(
        Metrics $structure,
        int $functions,
    ): string {
        $arr = $structure->toArray();

        return sprintf(
            <<<'EOT'
  Functions                                           %20s
    Named Functions                                   %20s (%.2f%%)
    Anonymous Functions                               %20s (%.2f%%)
EOT,
            number_format($functions),
            number_format($arr['namedFunctions']),
            self::pct($arr['namedFunctions'], $functions),
            number_format($arr['anonymousFunctions']),
            self::pct($arr['anonymousFunctions'], $functions),
        );
    }

    private function formatConstants(
        Metrics $structure,
        int $constants,
        int $classConstants,
    ): string {
        $arr = $structure->toArray();

        return sprintf(
            <<<'EOT'
  Constants                                           %20s
    Global Constants                                  %20s (%.2f%%)
    Class Constants                                   %20s (%.2f%%)
      Public Constants                                %20s (%.2f%%)
      Non-Public Constants                            %20s (%.2f%%)
EOT,
            number_format($constants),
            number_format($arr['globalConstants']),
            self::pct($arr['globalConstants'], $constants),
            number_format($classConstants),
            self::pct($classConstants, $constants),
            number_format($arr['publicClassConstants']),
            self::pct($arr['publicClassConstants'], $classConstants),
            number_format($arr['nonPublicClassConstants']),
            self::pct($arr['nonPublicClassConstants'], $classConstants),
        );
    }
}
