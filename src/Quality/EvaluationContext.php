<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Bunnivo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Result;

final readonly class EvaluationContext
{
    public function __construct(
        public QualityConfig $config,
        public Result $projectMetrics,
        public EvaluationContext\FileMetrics $fileMetrics,
    ) {}

}
