<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

final readonly class LocMetrics
{
    /**
     * @param array{directories: int, files: int, linesOfCode: int, commentLinesOfCode: int, nonCommentLinesOfCode: int, logicalLinesOfCode: int} $data
     */
    public function __construct(private array $data) {}

    /**
     * @return array{directories: int, files: int, linesOfCode: int, commentLinesOfCode: int, nonCommentLinesOfCode: int, logicalLinesOfCode: int}
     */
    public function stats(): array
    {
        return $this->data;
    }

    /**
     * @return array{comment: float, nonComment: float, logical: float}
     */
    public function percentages(): array
    {
        $loc = $this->data['linesOfCode'];
        if ($loc <= 0) {
            return ['comment' => 0.0, 'nonComment' => 0.0, 'logical' => 0.0];
        }

        return [
            'comment'    => ($this->data['commentLinesOfCode'] / $loc) * 100,
            'nonComment' => ($this->data['nonCommentLinesOfCode'] / $loc) * 100,
            'logical'    => ($this->data['logicalLinesOfCode'] / $loc) * 100,
        ];
    }
}
