<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use function explode;

use Illuminate\Support\Collection;

use function str_contains;

final class MethodChecker
{
    public function __construct(
        private readonly QualityConfig $config,
    ) {}

    /**
     * @psalm-param array<string, array{loc: int, args: int}> $methods
     * @psalm-param array<string, positive-int> $complexityByMethod
     *
     * @return Collection<int, Violation>
     */
    public function check(string $file, array $methods, array $complexityByMethod): Collection
    {
        $maxLoc = $this->config->getRule('max_method_length');
        $maxArgs = $this->config->getRule('max_arguments');

        return collect($methods)
            ->flatMap(function (array $data, string $name) use ($file, $maxLoc, $maxArgs, $complexityByMethod) {
                $info = [
                    'fullName' => $name,
                    'class'    => str_contains($name, '::') ? explode('::', $name)[0] : null,
                    'loc'      => $data['loc'],
                    'args'     => $data['args'],
                ];

                return array_merge(
                    $this->checkLoc($file, $info, $maxLoc),
                    $this->checkArgs($file, $info, $maxArgs),
                    $this->checkComplexity($file, $info, $complexityByMethod[$name] ?? 1),
                );
            })
            ->values();
    }

    /**
     * @psalm-param array{fullName: string, class: string|null, loc: int} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkLoc(string $file, array $info, int $max): array
    {
        return $this
            ->whenExceeded('max_method_length')
            ->file($file)
            ->method($info['fullName'])
            ->class($info['class'])
            ->forValue($info['loc'])
            ->limit($max)
            ->result();
    }

    /**
     * @psalm-param array{fullName: string, class: string|null, args: int} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkArgs(string $file, array $info, int $max): array
    {
        return $this
            ->whenExceeded('max_arguments')
            ->file($file)
            ->method($info['fullName'])
            ->class($info['class'])
            ->forValue($info['args'])
            ->limit($max)
            ->result();
    }

    /**
     * @psalm-param array{fullName: string, class: string|null} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkComplexity(string $file, array $info, int $complexity): array
    {
        $maxComplexity = $this->config->getRule('max_cyclomatic_complexity');

        return $this
            ->whenExceeded('max_cyclomatic_complexity')
            ->file($file)
            ->method($info['fullName'])
            ->class($info['class'])
            ->forValue($complexity)
            ->limit($maxComplexity)
            ->result();
    }

    private function whenExceeded(string $rule): RuleChecker
    {
        return RuleChecker::whenExceeded($rule);
    }
}
