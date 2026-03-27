<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Limits;
use Bunnivo\Soda\Quality\Report\Violation;
use Bunnivo\Soda\Quality\Report\ViolationBuilder;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Illuminate\Support\Collection;

/**
 * Simplified base class for writing custom rules.
 *
 * Extend this class and implement just two methods:
 *   - {@see id()} — unique rule identifier (snake_case string)
 *   - {@see evaluate()} — return violations for a single file
 *
 * Use the built-in helpers {@see exceeds()} and {@see below()} to build
 * violations without touching the internal API.
 *
 * @example
 *
 *   final class MaxFileLoc extends SodaRule
 *   {
 *       public function id(): string { return 'max_file_loc_custom'; }
 *
 *       protected function evaluate(string $file, array $metrics): array
 *       {
 *           return $this->exceeds($file, $metrics['file_loc'], 300);
 *       }
 *   }
 *
 * Register in soda.php:
 *
 *   $config->rule(MaxFileLoc::class);
 *
 * Available keys in $metrics per file:
 *   - file_loc          int   Total lines of code
 *   - classes_count     int   Number of classes
 *   - classes           array Per-class data (loc, methods, properties, dependencies, …)
 *   - methods           array Per-method data (loc, args)
 *   - namespaces        array Namespace → count
 *   - breathing         array Breathing metric scores (CBS, VBI, IRS, COL…)
 */
abstract class SodaRule implements RuleChecker
{
    /**
     * Unique rule identifier in snake_case, e.g. `'my_max_file_size'`.
     */
    abstract public function id(): string;

    /**
     * Evaluate a single file and return any violations.
     *
     * @param array<string, mixed> $metrics Raw metrics for this file (see class docblock for available keys).
     *
     * @return list<Violation>
     */
    abstract protected function evaluate(string $file, array $metrics): array;

    /**
     * Returns a violation when $value **exceeds** $limit, or an empty array when the check passes.
     *
     * @return list<Violation>
     */
    final protected function exceeds(
        string $file,
        int|float $value,
        int|float $limit,
        ?string $class = null,
        ?string $method = null,
        ?int $line = null,
    ): array {
        if ($value <= $limit) {
            return [];
        }

        return [$this->buildViolation($file, $value, $limit, $class, $method, $line)];
    }

    /**
     * Returns a violation when $value falls **below** $limit, or an empty array when the check passes.
     *
     * @return list<Violation>
     */
    final protected function below(
        string $file,
        int|float $value,
        int|float $limit,
        ?string $class = null,
        ?string $method = null,
        ?int $line = null,
    ): array {
        if ($value >= $limit) {
            return [];
        }

        return [$this->buildViolation($file, $value, $limit, $class, $method, $line)];
    }

    /**
     * Iterates all files and collects violations from {@see evaluate()}.
     *
     * @return Collection<int, Violation>
     */
    #[\Override]
    final public function check(EvaluationContext $context): Collection
    {
        $violations = [];

        foreach ($context->fileMetrics->qualityMetrics() as $file => $metrics) {
            array_push($violations, ...$this->evaluate($file, $metrics));
        }

        return collect($violations);
    }

    private function buildViolation(
        string $file,
        int|float $value,
        int|float $limit,
        ?string $class,
        ?string $method,
        ?int $line,
    ): Violation {
        return ViolationBuilder::of(
            $this->id(),
            $file,
            new Limits(max(1, (int) round($value)), max(1, (int) round($limit))),
        )
            ->atClass($class)
            ->atMethod($method)
            ->atLine($line)
            ->build();
    }
}
