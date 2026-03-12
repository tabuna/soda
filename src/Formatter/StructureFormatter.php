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

        $classes = $structure->classes();
        $concrete = $structure->concreteClasses();
        $methods = $structure->methods();
        $functions = $structure->functions();
        $constants = $structure->constants();
        $classConstants = $structure->classConstants();

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
            number_format($structure->namespaces()),
            number_format($structure->interfaces()),
            number_format($structure->traits()),
            number_format($classes),
            number_format($structure->abstractClasses()),
            self::pct($structure->abstractClasses(), $classes),
            number_format($concrete),
            self::pct($concrete, $classes),
            number_format($structure->finalClasses()),
            self::pct($structure->finalClasses(), $concrete),
            number_format($structure->nonFinalClasses()),
            self::pct($structure->nonFinalClasses(), $concrete),
        );
    }

    private function formatMethods(
        Metrics $structure,
        int $methods,
    ): string {
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
            number_format($structure->nonStaticMethods()),
            self::pct($structure->nonStaticMethods(), $methods),
            number_format($structure->staticMethods()),
            self::pct($structure->staticMethods(), $methods),
            number_format($structure->publicMethods()),
            self::pct($structure->publicMethods(), $methods),
            number_format($structure->protectedMethods()),
            self::pct($structure->protectedMethods(), $methods),
            number_format($structure->privateMethods()),
            self::pct($structure->privateMethods(), $methods),
        );
    }

    private function formatFunctions(
        Metrics $structure,
        int $functions,
    ): string {
        return sprintf(
            <<<'EOT'
  Functions                                           %20s
    Named Functions                                   %20s (%.2f%%)
    Anonymous Functions                               %20s (%.2f%%)
EOT,
            number_format($functions),
            number_format($structure->namedFunctions()),
            self::pct($structure->namedFunctions(), $functions),
            number_format($structure->anonymousFunctions()),
            self::pct($structure->anonymousFunctions(), $functions),
        );
    }

    private function formatConstants(
        Metrics $structure,
        int $constants,
        int $classConstants,
    ): string {
        return sprintf(
            <<<'EOT'
  Constants                                           %20s
    Global Constants                                  %20s (%.2f%%)
    Class Constants                                   %20s (%.2f%%)
      Public Constants                                %20s (%.2f%%)
      Non-Public Constants                            %20s (%.2f%%)
EOT,
            number_format($constants),
            number_format($structure->globalConstants()),
            self::pct($structure->globalConstants(), $constants),
            number_format($classConstants),
            self::pct($classConstants, $constants),
            number_format($structure->publicClassConstants()),
            self::pct($structure->publicClassConstants(), $classConstants),
            number_format($structure->nonPublicClassConstants()),
            self::pct($structure->nonPublicClassConstants(), $classConstants),
        );
    }
}
