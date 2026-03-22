<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Ast;

final readonly class QualityAstVisitors
{
    public function __construct(
        public QualityAstVisitorBundle $astVisitorBundle,
    ) {}

    public static function create(int $logicalLines): self
    {
        return new self(QualityAstVisitorBundle::forLogicalLines($logicalLines));
    }
}
