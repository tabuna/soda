<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

use Bunnivo\Soda\Quality\ConfigException;
use Bunnivo\Soda\Quality\Rule\RuleChecker;

use function is_readable;

/**
 * Loads extra {@see RuleChecker} from the **consumer project's** `config/soda.php` (`rules` => class names).
 * Not shipped inside the package; optional hook for apps that install Soda via Composer.
 */
final class PhpSodaConfig
{
    /**
     * @return list<RuleChecker>
     */
    public static function checkersFromPath(?string $absolutePath): array
    {
        if ($absolutePath === null || $absolutePath === '' || ! is_readable($absolutePath)) {
            return [];
        }

        return self::checkersFromLoadedConfig(require $absolutePath);
    }

    /**
     * @return list<RuleChecker>
     */
    private static function checkersFromLoadedConfig(mixed $config): array
    {
        throw_unless(is_array($config), ConfigException::class, 'config/soda.php must return an array.');

        return self::instantiateRuleClasses(self::normalisedRuleClassList($config));
    }

    /**
     * @param array<mixed, mixed> $config
     *
     * @return list<string>
     */
    private static function normalisedRuleClassList(array $config): array
    {
        $rules = $config['rules'] ?? [];

        if ($rules === []) {
            return [];
        }

        throw_unless(is_array($rules), ConfigException::class, 'config/soda.php key "rules" must be an array of class names.');

        return $rules;
    }

    /**
     * @param list<string> $rules
     *
     * @return list<RuleChecker>
     */
    private static function instantiateRuleClasses(array $rules): array
    {
        $checkers = [];

        foreach ($rules as $class) {
            throw_if(! is_string($class) || $class === '', ConfigException::class, 'Each entry in config/soda.php "rules" must be a non-empty class string.');
            $checkers[] = self::instantiateOneChecker($class);
        }

        return $checkers;
    }

    private static function instantiateOneChecker(string $class): RuleChecker
    {
        if (! class_exists($class)) {
            throw new ConfigException(sprintf('Rule class not found: %s', $class));
        }

        $instance = new $class;

        if (! $instance instanceof RuleChecker) {
            throw new ConfigException(sprintf('%s must implement %s.', $class, RuleChecker::class));
        }

        return $instance;
    }
}
