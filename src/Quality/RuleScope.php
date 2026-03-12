<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

/**
 * @internal
 */
final readonly class RuleScope
{
    public function __construct(
        private string $file,
        private RuleContext $context,
        private Threshold $threshold,
    ) {}

    public static function empty(): self
    {
        return new self('', RuleContext::empty(), Threshold::empty());
    }

    public function file(): string
    {
        return $this->file;
    }

    public function context(): RuleContext
    {
        return $this->context;
    }

    public function threshold(): Threshold
    {
        return $this->threshold;
    }

    public function withFile(string $file): self
    {
        return new self($file, $this->context, $this->threshold);
    }

    public function withContext(?string $class, ?string $method): self
    {
        return new self(
            $this->file,
            $this->context->withClass($class)->withMethod($method),
            $this->threshold,
        );
    }

    public function withValue(int $value): self
    {
        return new self($this->file, $this->context, $this->threshold->withValue($value));
    }

    public function withLimit(int|float $limit): self
    {
        return new self($this->file, $this->context, $this->threshold->withLimit($limit));
    }

    public function withLine(?int $line): self
    {
        return new self($this->file, $this->context->withLine($line), $this->threshold);
    }
}
