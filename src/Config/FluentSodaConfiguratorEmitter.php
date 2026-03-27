<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use Bunnivo\Soda\Quality\Config\RuleSections;
use LogicException;

/**
 * Builds fluent {@see SodaConfigurator} PHP source from nested section payloads.
 *
 * @internal
 */
final class FluentSodaConfiguratorEmitter
{
    /**
     * @param array<string, array<string, mixed>> $sections
     */
    public static function emitSodaPhpFile(string $rulesClassName, array $sections): string
    {
        $body = self::emitConfigureBody($sections);

        return <<<PHP
<?php

declare(strict_types=1);

use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Config\SodaConfigurator;

class {$rulesClassName} extends SodaConfigurator
{
    protected function configure(SodaConfig \$config): void
    {
{$body}
    }
}

return SodaConfigurator::entry({$rulesClassName}::class);

PHP;
    }

    /**
     * @param array<string, array<string, mixed>> $sections
     */
    public static function emitConfigureBody(array $sections, string $lineIndent = '        '): string
    {
        $lines = [];

        foreach (RuleSections::sections() as $section => $ruleIds) {
            foreach ($ruleIds as $ruleId) {
                if (! isset($sections[$section][$ruleId])) {
                    continue;
                }

                $lines[] = $lineIndent.self::emitLine($section, $ruleId, $sections[$section][$ruleId]);
            }
        }

        return implode("\n", $lines);
    }

    private static function emitLine(string $section, string $ruleId, mixed $value): string
    {
        return match ($section) {
            RuleSections::STRUCTURAL   => StructuralRuleStatementPhpEmitter::emit($ruleId, $value),
            RuleSections::COMPLEXITY   => ComplexityRuleStatementPhpEmitter::emit($ruleId, $value),
            RuleSections::BREATHING    => BreathingRuleStatementPhpEmitter::emit($ruleId, $value),
            RuleSections::NAMING       => NamingRuleStatementPhpEmitter::emit($ruleId, $value),
            default                    => throw new LogicException('Unknown section: '.$section),
        };
    }
}
