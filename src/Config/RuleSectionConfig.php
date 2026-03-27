<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

/**
 * @internal
 */
abstract class RuleSectionConfig
{
    /**
     * @var array<string, mixed>
     */
    protected array $entries = [];

    /**
     * @return array<string, mixed>
     */
    public function toSectionArray(): array
    {
        return $this->entries;
    }
}
