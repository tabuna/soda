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

final readonly class Limits
{
    /**
     * @psalm-param positive-int $value
     * @psalm-param positive-int $threshold
     */
    public function __construct(
        public int $value,
        public int $threshold,
    ) {}
}
