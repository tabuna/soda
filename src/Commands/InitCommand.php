<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Commands;

use function file_put_contents;
use function getcwd;

use Illuminate\Console\Command;

use function json_encode;

final class InitCommand extends Command
{
    protected $signature = 'init';

    protected $description = 'Create soda.json with default quality rules';

    private const array INIT_CONFIG = [
        'quality' => [
            'min_score' => 80,
        ],
        'rules' => [
            'max_method_length'                    => 120,
            'max_class_length'                     => 500,
            'max_arguments'                        => 16,
            'max_methods_per_class'                => 21,
            'max_file_loc'                         => 400,
            'max_cyclomatic_complexity'            => 26,
            'max_properties_per_class'             => 20,
            'max_public_methods'                   => 15,
            'max_dependencies'                     => 20,
            'max_classes_per_file'                 => 1,
            'max_namespace_depth'                  => 4,
            'max_classes_per_namespace'            => 40,
            'max_traits_per_class'                 => 3,
            'max_interfaces_per_class'             => 5,
            'max_classes_per_project'              => 2000,
            'min_code_breathing_score'             => 40,
            'min_visual_breathing_index'           => 12,
            'min_identifier_readability_score'     => 75,
            'min_code_oxygen_level'                => 25,
            'max_weighted_cognitive_density'       => 30,
            'max_logical_complexity_factor'        => 35,
        ],
    ];

    public function handle(): int
    {
        $failure = $this->validateOrFail();
        if ($failure !== null) {
            return $failure;
        }

        $path = getcwd().'/soda.json';
        $json = json_encode(self::INIT_CONFIG, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
