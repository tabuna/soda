<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Plugins\Rules;

use Bunnivo\Soda\Config\SodaRule;
use Bunnivo\Soda\Config\ViolationAt;
use Bunnivo\Soda\Plugins\Rules\UselessVariable\UselessVariableAnalyser;

/**
 * Detects useless variables — direct copies of another variable ($a = $b)
 * that can be safely removed by replacing all usages of $a with $b.
 *
 * A variable is considered useless when:
 *   - It is assigned directly from another variable: $a = $b
 *   - It is never mutated, reassigned, or incremented
 *   - It is never passed by reference
 *   - It is not captured by a closure or arrow function
 *   - The source variable ($b) is not unset before $a is used
 *
 * @example Register in soda.php:
 *
 *   $config->rule(\Bunnivo\Soda\Plugins\Rules\UselessVariableRule::class);
 */
final class UselessVariableRule extends SodaRule
{
    #[\Override]
    public function id(): string
    {
        return 'useless_variable';
    }

    #[\Override]
    protected function analyze(string $file): array
    {
        $found = (new UselessVariableAnalyser)->analyse($this->parse($file));

        return ['useless_variables' => $found];
    }

    #[\Override]
    protected function evaluate(string $file, array $metrics): array
    {
        $violations = [];

        foreach ($metrics['useless_variables'] ?? [] as $v) {
            array_push($violations, ...$this->exceeds($file, 1, 0, new ViolationAt(line: $v['line'])));
        }

        return $violations;
    }
}
