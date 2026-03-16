<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Commands;

use Bunnivo\Soda\Quality\QualityConfig;
use Bunnivo\Soda\Quality\RuleSections;

use function file_put_contents;
use function getcwd;

use Illuminate\Console\Command;

use function json_encode;

final class InitCommand extends Command
{
    protected $signature = 'init';

    protected $description = 'Create soda.json with default quality rules';

    private function buildRulesConfig(): array
    {
        $defaults = QualityConfig::default()->rules;
        $overrides = [
            'max_method_length'         => 120,
            'max_class_length'          => 500,
            'max_arguments'             => 16,
            'max_methods_per_class'     => 21,
            'max_file_loc'              => 400,
            'max_cyclomatic_complexity' => 26,
            'max_control_nesting'       => 4,
            'min_code_breathing_score'  => 40,
        ];

        $rules = [
            RuleSections::STRUCTURAL => [],
            RuleSections::COMPLEXITY => [],
            RuleSections::BREATHING  => [],
        ];

        foreach (RuleSections::ruleToSection() as $ruleKey => $section) {
            $value = $overrides[$ruleKey] ?? $defaults[$ruleKey] ?? null;

            if ($value !== null) {
                $rules[$section][$ruleKey] = $value;
            }
        }

        return $rules;
    }

    public function handle(): int
    {
        $failure = $this->validateOrFail();
        if ($failure !== null) {
            return $failure;
        }

        $path = getcwd().'/soda.json';
        $config = [
            'quality' => ['min_score' => 80],
            'rules'   => $this->buildRulesConfig(),
        ];
        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json) === false) {
            $this->error($json === false ? 'Failed to encode config' : 'Failed to write soda.json');

            return self::FAILURE;
        }

        $this->info('Created soda.json');

        return self::SUCCESS;
    }

    private function validateOrFail(): ?int
    {
        $cwd = getcwd();
        /** @psalm-suppress TypeDoesNotContainType - getcwd() can return '' on edge cases */
        if ($cwd === false || $cwd === '') {
            $this->error('Cannot determine current directory');

            return self::FAILURE;
        }

        if (is_readable($cwd.'/soda.json')) {
            $this->error('soda.json already exists');

            return self::FAILURE;
        }

        return null;
    }
}
