<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Limits;
use Bunnivo\Soda\Quality\Report\Violation;
use Bunnivo\Soda\Quality\Report\ViolationBuilder;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Illuminate\Support\Collection;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\ParserFactory;

/**
 * Base class for writing custom quality rules.
 *
 * Two required methods:
 *   - {@see id()}       — unique rule ID (snake_case)
 *   - {@see evaluate()} — check one file, return violations
 *
 * One optional override for collecting your own metrics:
 *   - {@see analyze()}  — parse the file any way you like, return custom data
 *                         merged into $metrics before evaluate() is called
 *
 * Built-in helpers:
 *   - {@see exceeds()} / {@see below()} — build violations in one line
 *   - {@see contents()}                 — read raw file source
 *   - {@see parse()}                    — parse file into PHP-Parser AST
 *
 * @example Rule using only built-in metrics:
 *
 *   final class MaxFileLoc extends SodaRule
 *   {
 *       public function id(): string { return 'max_file_loc'; }
 *
 *       protected function evaluate(string $file, array $metrics): array
 *       {
 *           return $this->exceeds($file, $metrics['file_loc'], 300);
 *       }
 *   }
 * @example Rule that collects its own metrics (fully independent of built-in data):
 *
 *   final class NoVarDumpRule extends SodaRule
 *   {
 *       public function id(): string { return 'no_var_dump'; }
 *
 *       protected function analyze(string $file): array
 *       {
 *           preg_match_all('/var_dump\s*\(/', $this->contents($file), $m);
 *           return ['var_dump_count' => count($m[0])];
 *       }
 *
 *       protected function evaluate(string $file, array $metrics): array
 *       {
 *           return $this->exceeds($file, $metrics['var_dump_count'], 0);
 *       }
 *   }
 * @example Rule using the PHP-Parser AST:
 *
 *   final class NoGlobalKeywordRule extends SodaRule
 *   {
 *       public function id(): string { return 'no_global_keyword'; }
 *
 *       protected function analyze(string $file): array
 *       {
 *           $count = 0;
 *           foreach ($this->parse($file) as $node) {
 *               if ($node instanceof \PhpParser\Node\Stmt\Global_) {
 *                   $count++;
 *               }
 *           }
 *           return ['global_count' => $count];
 *       }
 *
 *       protected function evaluate(string $file, array $metrics): array
 *       {
 *           return $this->exceeds($file, $metrics['global_count'], 0);
 *       }
 *   }
 */
abstract class SodaRule implements RuleChecker
{
    /**
     * Unique rule identifier in snake_case, e.g. `'no_var_dump'`.
     */
    abstract public function id(): string;

    /**
     * Collect custom metrics for a single file.
     *
     * Override this to gather your own data — regex, token analysis,
     * AST traversal, file size checks, anything you need.
     *
     * The returned array is **merged into $metrics** before {@see evaluate()} is called.
     * You are not limited to any predefined keys — define whatever you need.
     *
     * @return array<string, mixed>
     */
    protected function analyze(string $file): array
    {
        return [];
    }

    /**
     * Evaluate a single file and return violations.
     *
     * Receives built-in metrics merged with your {@see analyze()} output.
     *
     * Built-in keys: file_loc, classes_count, classes[], methods[], namespaces[], breathing[].
     * Your own keys: whatever you returned from analyze().
     *
     * @param array<string, mixed> $metrics
     *
     * @return list<Violation>
     */
    abstract protected function evaluate(string $file, array $metrics): array;

    /**
     * Returns a violation when $value **exceeds** $limit, or [] when the check passes.
     *
     * @return list<Violation>
     */
    final protected function exceeds(
        string $file,
        int|float $value,
        int|float $limit,
        ?ViolationAt $at = null,
    ): array {
        if ($value <= $limit) {
            return [];
        }

        return [$this->buildViolation($file, $value, $limit, $at)];
    }

    /**
     * Returns a violation when $value falls **below** $limit, or [] when the check passes.
     *
     * @return list<Violation>
     */
    final protected function below(
        string $file,
        int|float $value,
        int|float $limit,
        ?ViolationAt $at = null,
    ): array {
        if ($value >= $limit) {
            return [];
        }

        return [$this->buildViolation($file, $value, $limit, $at)];
    }

    /**
     * Read the raw source of a file.
     *
     * Use in {@see analyze()} for regex or string-based analysis.
     */
    final protected function contents(string $file): string
    {
        $src = @file_get_contents($file);

        return $src !== false ? $src : '';
    }

    /**
     * Parse a PHP file into a PHP-Parser AST node list.
     *
     * Use in {@see analyze()} for structural/syntactic analysis.
     * Returns [] if the file cannot be read or parsed.
     *
     * @return list<Node>
     */
    final protected function parse(string $file): array
    {
        $source = $this->contents($file);

        if ($source === '') {
            return [];
        }

        try {
            return (new ParserFactory)->createForNewestSupportedVersion()->parse($source) ?? [];
        } catch (Error) {
            return [];
        }
    }

    /**
     * Iterates all files, merges built-in metrics with {@see analyze()} output,
     * then calls {@see evaluate()}.
     *
     * @return Collection<int, Violation>
     */
    #[\Override]
    final public function check(EvaluationContext $context): Collection
    {
        $violations = [];

        foreach ($context->fileMetrics->qualityMetrics() as $file => $builtinMetrics) {
            $metrics = array_merge($builtinMetrics, $this->analyze($file));
            array_push($violations, ...$this->evaluate($file, $metrics));
        }

        return collect($violations);
    }

    private function buildViolation(
        string $file,
        int|float $value,
        int|float $limit,
        ?ViolationAt $at,
    ): Violation {
        return ViolationBuilder::of(
            $this->id(),
            $file,
            new Limits(max(1, (int) round($value)), max(1, (int) round($limit))),
        )
            ->atClass($at?->class)
            ->atMethod($at?->method)
            ->atLine($at?->line)
            ->build();
    }
}
