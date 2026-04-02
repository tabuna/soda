<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Result;

final readonly class EvaluationContext
{
    public function __construct(
        public QualityConfig $config,
        public Result $projectMetrics,
        public EvaluationContext\FileMetrics $fileMetrics,
    ) {}

    public function withConfig(QualityConfig $config): self
    {
        return new self($config, $this->projectMetrics, $this->fileMetrics);
    }
}
