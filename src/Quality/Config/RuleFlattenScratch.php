<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Config;

/**
 * Mutable accumulator while flattening nested `rules` from soda.json.
 */
final class RuleFlattenScratch
{
    /**
     * @var array<string, int|float>
     */
    public array $thresholds = [];

    /**
     * @var list<string>
     */
    public array $disabled = [];

    /**
     * @var array<string, array{files: list<string>, classes: list<string>, methods: list<string>}>
     */
    public array $exceptions = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    public array $options = [];
}
