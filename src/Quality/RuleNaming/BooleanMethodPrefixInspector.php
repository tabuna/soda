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
    private const array ALLOWED_PREFIXES = ['is', 'has', 'should', 'can', 'try'];

    /**
     * @param array<string, true>                                                        $methodExceptions
     * @param array<string, true>                                                        $classExceptions
     * @param array<string, true>                                                        $fileExceptions
     * @param array<string, array{inherits: list<string>, methods: array<string, true>}> $typeIndex
     */
    private int $threshold = 0;

    /**
     * @var array<string, true>
     */
    private array $methodExceptions = [];

    /**
     * @var array<string, true>
     */
    private array $classExceptions = [];

    /**
     * @var array<string, true>
     */
    private array $fileExceptions = [];

    /**
     * @var array<string, array{inherits: list<string>, methods: array<string, true>}>
     */
    private array $typeIndex = [];

    private function __construct() {}

    public static function fromContext(EvaluationContext $context): self
    {
        $exceptions = $context->config->ruleExceptions(BooleanMethodPrefixChecker::RULE);
        $self = new self;
        $self->threshold = (int) $context->config->getRule(BooleanMethodPrefixChecker::RULE);
        $self->methodExceptions = array_fill_keys($exceptions['methods'], true);
        $self->classExceptions = array_fill_keys($exceptions['classes'], true);
        $self->fileExceptions = array_fill_keys($exceptions['files'], true);
        $self->typeIndex = BooleanMethodTypeIndexBuilder::build($context);

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
        if (isset($this->fileExceptions[$file])) {
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

        return (is_string($methodName) && isset($this->methodExceptions[$methodName]))
            || (is_string($fullName) && isset($this->methodExceptions[$fullName]))
            || (is_string($className) && isset($this->classExceptions[$className]));
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
        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($methodName, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
