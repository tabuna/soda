<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

use Bunnivo\Soda\Quality\RuleCatalog\RuleCatalog;

/**
 * Defines rule sections for soda.php / merged rules payload.
 *
 * @internal
 */
final readonly class RuleSections
{
    public const string STRUCTURAL = 'structural';

    public const string COMPLEXITY = 'complexity';

    public const string BREATHING = 'breathing';

    public const string NAMING = 'naming';

    /**
     * @return array<string, list<string>>
     */
    public static function sections(): array
    {
        return RuleCatalog::sectionsOrdered();
    }

    /**
     * @return list<string>
     */
    public static function sectionNames(): array
    {
        return array_keys(self::sections());
    }

    /**
     * @return array<string, string> Map rule key -> section name
     */
    public static function ruleToSection(): array
    {
        $map = [];

        foreach (self::sections() as $section => $rules) {
            foreach ($rules as $rule) {
                $map[$rule] = $section;
            }
        }

        return $map;
    }
}
