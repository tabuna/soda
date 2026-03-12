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

namespace Bunnivo\Soda;

use function array_sum;
use function count;
use function max;
use function min;

use SebastianBergmann\Complexity\ComplexityCollection;

final class ComplexityStatistics
{
    /**
     * @psalm-return array{minimum: non-negative-int, maximum: non-negative-int, average: float}
     */
    public static function from(ComplexityCollection $items): array
    {
        $values = [];
        foreach ($items as $item) {
            $values[] = $item->cyclomaticComplexity();
        }

        return [
            'minimum' => ! empty($values) ? min($values) : 0,
            'maximum' => ! empty($values) ? max($values) : 0,
            'average' => ! empty($values) ? array_sum($values) / count($values) : 0,
        ];
    }
}
