<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Commands;

use Bunnivo\Soda\Quality\Rule\RuleCatalog;
use Illuminate\Console\Command;

final class ListRulesCommand extends Command
{
    protected $signature = 'list:rules';

    protected $description = 'List built-in quality rule ids (from RuleCatalog)';

    public function handle(): int
    {
        $rows = [];

        foreach (RuleCatalog::definitions() as $id => $definition) {
            $rows[] = [$id, $definition->fields->identity->section, $definition->fields->presentation->label];
        }

        $this->table(['Rule id', 'Section', 'Label'], $rows);

        return self::SUCCESS;
    }
}
