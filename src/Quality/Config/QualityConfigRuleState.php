<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

final readonly class QualityConfigRuleState
{
    /**
     * @param array<string, array{files: list<string>, classes: list<string>, methods: list<string>}> $exceptions
     * @param array<string, array<string, mixed>>                                                     $options
     */
    public function __construct(
        public array $exceptions = [],
        public array $options = [],
    ) {}
}
