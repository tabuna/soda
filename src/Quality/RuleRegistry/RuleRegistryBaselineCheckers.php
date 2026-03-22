<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleRegistry;

use Bunnivo\Soda\Quality\Rule\RuleChecker;

/**
 * Checkers that can be instantiated without config-specific constructor arguments.
 *
 * @internal
 */
final class RuleRegistryBaselineCheckers
{
    /**
     * @var list<non-empty-string>
     */
    private const array CHECKER_NAMES = [
        'LocChecker',
        'BreathingChecker',
        'ClassesChecker',
        'TodoCommentChecker',
        'CommentedCodeChecker',
        'EmptyCatchChecker',
        'AskThenTellChecker',
        'LayerMixingChecker',
        'RedundantNamingChecker',
        'BooleanMethodPrefixChecker',
        'NamespaceChecker',
        'ProjectChecker',
    ];

    private const string CHECKER_NAMESPACE = 'Bunnivo\\Soda\\Quality\\Rule\\';

    /**
     * @return list<RuleChecker>
     */
    public static function all(): array
    {
        $checkers = [];

        foreach (self::CHECKER_NAMES as $checkerName) {
            $checkers[] = self::instantiate(self::checkerClass($checkerName));
        }

        return $checkers;
    }

    /**
     * @param class-string<RuleChecker> $checkerClass
     */
    private static function instantiate(string $checkerClass): RuleChecker
    {
        return new $checkerClass;
    }

    /**
     * @return class-string<RuleChecker>
     */
    private static function checkerClass(string $checkerName): string
    {
        /** @var class-string<RuleChecker> $checkerClass */
        $checkerClass = self::CHECKER_NAMESPACE.$checkerName;

        return $checkerClass;
    }
}
