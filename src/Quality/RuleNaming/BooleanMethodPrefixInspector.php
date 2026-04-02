<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleNaming;

use function array_fill_keys;

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Rule\BooleanMethodPrefixChecker;

use function str_starts_with;

/**
 * @internal
 */
final class BooleanMethodPrefixInspector
{
    private const array DEFAULT_PREFIXES = ['is', 'has', 'should', 'can', 'try'];

    private int $threshold = 0;

    /**
     * Keyed exception sets: 'methods', 'classes', 'files' → map of name → true.
     *
     * @var array<string, array<string, true>>
     */
    private array $except = [];

    /**
     * @var array<string, array{inherits: list<string>, methods: array<string, true>}>
     */
    private array $typeIndex = [];

    /** @var list<string> */
    private array $allowedPrefixes = self::DEFAULT_PREFIXES;

    private function __construct() {}

    public static function fromContext(EvaluationContext $context): self
    {
        $exceptions = $context->config->ruleExceptions(BooleanMethodPrefixChecker::RULE);
        $self = new self;
        $self->threshold = (int) $context->config->getRule(BooleanMethodPrefixChecker::RULE);
        $self->except = [
            'methods' => array_fill_keys($exceptions['methods'] ?? [], true),
            'classes' => array_fill_keys($exceptions['classes'] ?? [], true),
            'files'   => array_fill_keys($exceptions['files'] ?? [], true),
        ];
        $self->typeIndex = BooleanMethodTypeIndexBuilder::build($context);

        if (isset($exceptions['prefixes']) && is_array($exceptions['prefixes'])) {
            /** @var list<string> $prefixes */
            $prefixes = array_values(array_filter($exceptions['prefixes'], is_string(...)));
            $self->allowedPrefixes = $prefixes !== [] ? $prefixes : self::DEFAULT_PREFIXES;
        }

        return $self;
    }

    public function threshold(): int
    {
        return $this->threshold;
    }

    /**
     * @param array<string, mixed> $metrics
     *
     * @return list<array<string, mixed>>
     */
    public function violationsForFile(string $file, array $metrics): array
    {
        $exceptFiles = $this->except['files'] ?? [];
        if (isset($exceptFiles[$file])) {
            return [];
        }

        $badMethods = [];

        foreach ($this->methodsFromMetrics($metrics) as $methodData) {
            if ($this->shouldReportMethod($methodData)) {
                $badMethods[] = $methodData;
            }
        }

        return $badMethods;
    }

    /**
     * @param array<string, mixed> $methodData
     */
    private function shouldReportMethod(array $methodData): bool
    {
        if (! $this->isBooleanClassMethod($methodData)) {
            return false;
        }

        $methodName = $methodData['methodName'];

        return ! $this->hasAllowedPrefix($methodName)
            && ! $this->isExcepted($methodData)
            && ! $this->isInheritedContract($methodData);
    }

    /**
     * @param array<string, mixed> $metrics
     *
     * @return list<array<string, mixed>>
     */
    private function methodsFromMetrics(array $metrics): array
    {
        $naming = $metrics['naming'] ?? null;
        $methods = is_array($naming) ? ($naming['methods'] ?? null) : null;

        return is_array($methods) ? $methods : [];
    }

    /**
     * @param array<string, mixed> $methodData
     */
    private function isBooleanClassMethod(array $methodData): bool
    {
        return ($methodData['class'] ?? null) !== null
            && ($methodData['returnType'] ?? null) === 'bool'
            && is_string($methodData['methodName'] ?? null);
    }

    /**
     * @param array<string, mixed> $methodData
     */
    private function isExcepted(array $methodData): bool
    {
        $methodName = $methodData['methodName'] ?? null;
        $fullName = $methodData['name'] ?? null;
        $className = $methodData['class'] ?? null;
        $exceptMethods = $this->except['methods'] ?? [];
        $exceptClasses = $this->except['classes'] ?? [];

        return (is_string($methodName) && isset($exceptMethods[$methodName]))
            || (is_string($fullName) && isset($exceptMethods[$fullName]))
            || (is_string($className) && isset($exceptClasses[$className]));
    }

    /**
     * @param array<string, mixed> $methodData
     */
    private function isInheritedContract(array $methodData): bool
    {
        if (($methodData['hasOverrideAttribute'] ?? false) === true) {
            return true;
        }

        $className = $methodData['class'] ?? null;
        $methodName = $methodData['methodName'] ?? null;

        return is_string($className)
            && is_string($methodName)
            && BooleanMethodInheritanceProbe::hasDeclaredMethod($this->typeIndex, $className, $methodName);
    }

    private function hasAllowedPrefix(string $methodName): bool
    {
        foreach ($this->allowedPrefixes as $prefix) {
            if (str_starts_with($methodName, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
