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
        private string $rule,
        private string $file,
        private ?string $class,
        private ?string $method,
        private ?int $value,
        private int|float|null $limit,
        private ?int $line,
        private ?string $severity,
        private ?string $message,
    ) {}

    public static function whenExceeded(string $rule): self
    {
        return new self($rule, '', null, null, null, null, null, null, null);
    }

    public function file(string $filePath): self
    {
        return new self(
            $this->rule,
            $filePath,
            $this->class,
            $this->method,
            $this->value,
            $this->limit,
            $this->line,
            $this->severity,
            $this->message,
        );
    }

    public function class(?string $className): self
    {
        return new self(
            $this->rule,
            $this->file,
            $className,
            $this->method,
            $this->value,
            $this->limit,
            $this->line,
            $this->severity,
            $this->message,
        );
    }

    public function method(?string $methodName): self
    {
        return new self(
            $this->rule,
            $this->file,
            $this->class,
            $methodName,
            $this->value,
            $this->limit,
            $this->line,
            $this->severity,
            $this->message,
        );
    }

    public function forValue(int|float $value): self
    {
        return new self(
            $this->rule,
            $this->file,
            $this->class,
            $this->method,
            (int) $value,
            $this->limit,
            $this->line,
            $this->severity,
            $this->message,
        );
    }

    public function limit(int|float $limit): self
    {
        return new self(
            $this->rule,
            $this->file,
            $this->class,
            $this->method,
            $this->value,
            $limit,
            $this->line,
            $this->severity,
            $this->message,
        );
    }

    public function line(?int $lineNumber): self
    {
        return new self(
            $this->rule,
            $this->file,
            $this->class,
            $this->method,
            $this->value,
            $this->limit,
            $lineNumber,
            $this->severity,
            $this->message,
        );
    }

    public function severity(?string $severity): self
    {
        return new self(
            $this->rule,
            $this->file,
            $this->class,
            $this->method,
            $this->value,
            $this->limit,
            $this->line,
            $severity,
            $this->message,
        );
    }

    public function message(?string $message): self
    {
        return new self(
            $this->rule,
            $this->file,
            $this->class,
            $this->method,
            $this->value,
            $this->limit,
            $this->line,
            $this->severity,
            $message,
        );
    }

    /**
     * @return list<Violation>
     */
    public function result(): array
    {
        $value = $this->value ?? 0;
        $limit = $this->limit ?? 0;

        if ($limit <= 0 || $value <= $limit) {
            return [];
        }

        $violation = ViolationBuilder::of($this->rule, $this->file, new Limits($value, (int) $limit))
            ->atClass($this->class)
            ->atMethod($this->method)
            ->build();

        return [$violation];
    }
}
