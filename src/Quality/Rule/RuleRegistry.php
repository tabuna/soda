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

namespace Bunnivo\Soda\Quality\Rule;

use Bunnivo\Soda\Quality\ClassChecker;
use Bunnivo\Soda\Quality\MethodChecker;
use Bunnivo\Soda\Quality\QualityConfig;

/**
 * Registry of all quality rule checkers.
 */
final class RuleRegistry
{
    /**
     * @return list<RuleChecker>
     */
    public static function default(QualityConfig $config): array
    {
        return [
            new FileLocChecker(),
            new ClassesPerFileChecker(),
            new ClassRulesChecker(new ClassChecker($config)),
            new MethodRulesChecker(new MethodChecker($config)),
            new NamespaceChecker(),
            new ProjectChecker(),
        ];
    }
}
