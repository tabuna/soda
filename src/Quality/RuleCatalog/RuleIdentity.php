<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleCatalog;

final readonly class RuleIdentity
{
    public function __construct(
        public string $id,
        public string $section,
    ) {}
}
