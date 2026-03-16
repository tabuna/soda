<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

/**
 * Fluent rule checker in Laravel style.
 *
 * @example
 *   RuleChecker::whenExceeded('max_class_length')
 *       ->file($filePath)
 *       ->class($className)
 *       ->forValue($linesOfCode)
 *       ->limit($maxLength)
 *       ->result();
 */
final readonly class RuleChecker
{
    private function __construct(
        private RuleCheckerState $state,
    ) {}

    public static function whenExceeded(string $rule): self
    {
        return new self(RuleCheckerState::create($rule, true));
    }

    public static function whenBelow(string $rule): self
    {
        return new self(RuleCheckerState::create($rule, false));
    }

    public function file(string $filePath): self
    {
        return new self($this->state->withScope(
            $this->state->scope()->withFile($filePath),
        ));
    }

    public function class(?string $className): self
    {
        $ctx = $this->state->context();

        return new self($this->state->withScope(
            $this->state->scope()->withContext($className, $ctx->method()),
        ));
    }

    public function method(?string $methodName): self
    {
        $ctx = $this->state->context();

        return new self($this->state->withScope(
            $this->state->scope()->withContext($ctx->class(), $methodName),
        ));
    }

    public function forValue(int|float $value): self
    {
        return new self($this->state->withScope(
            $this->state->scope()->withValue((int) round($value)),
        ));
    }

    public function limit(int|float $limit): self
    {
        return new self($this->state->withScope(
            $this->state->scope()->withLimit($limit),
        ));
    }

    public function line(?int $lineNumber): self
    {
        return new self($this->state->withScope(
            $this->state->scope()->withLine($lineNumber),
        ));
    }

    /**
     * @psalm-suppress UnusedParam Extensibility hook for severity/message
     */
    public function meta(): self
    {
        return $this;
    }

    /**
     * @return list<Violation>
     */
    public function result(): array
    {
        $threshold = $this->state->threshold();
        $value = $threshold->value() ?? 0;
        $limit = $threshold->limit() ?? 0;
        $thresholdInt = (int) round($limit);

        $violates = $this->state->exceededMode()
            ? ($limit > 0 && $value > $limit)
            : ($limit > 0 && $value < $limit);

        if (! $violates) {
            return [];
        }

        $context = $this->state->context();
        $violation = ViolationBuilder::of(
            $this->state->rule(),
            $this->state->file(),
            new Limits(max(1, $value), max(1, $thresholdInt)),
        )
            ->atClass($context->class())
            ->atMethod($context->method())
            ->atLine($context->line())
            ->build();

        return [$violation];
    }
}
