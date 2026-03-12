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

use function array_merge;
use function explode;
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
     * @psalm-return list<Violation>
     */
    public function check(string $file, array $methods, array $complexityByMethod): array
    {
        $violations = [];
        $maxMethodLength = $this->config->getRule('max_method_length');
        $maxArgs = $this->config->getRule('max_arguments');

        foreach ($methods as $fullName => $data) {
            $info = [
                'fullName' => $fullName,
                'class'    => str_contains($fullName, '::') ? explode('::', $fullName)[0] : null,
                'loc'      => $data['loc'],
                'args'     => $data['args'],
            ];
            $violations = array_merge(
                $violations,
                $this->checkMethodLength($file, $info, $maxMethodLength),
                $this->checkArgs($file, $info, $maxArgs),
                $this->checkComplexity($file, $info, $complexityByMethod[$fullName] ?? 1),
            );
        }

        return $violations;
    }

    /**
     * @psalm-param array{fullName: string, class: string|null, loc: int} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkMethodLength(string $file, array $info, int $max): array
    {
        if ($max <= 0 || $info['loc'] <= $max) {
            return [];
        }

        return [
            ViolationBuilder::of('max_method_length', $file, new Limits($info['loc'], $max))
                ->atMethod($info['fullName'])
                ->atClass($info['class'])
                ->build(),
        ];
    }

    /**
     * @psalm-param array{fullName: string, class: string|null, args: int} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkArgs(string $file, array $info, int $max): array
    {
        if ($max <= 0 || $info['args'] <= $max) {
            return [];
        }

        return [
            ViolationBuilder::of('max_arguments', $file, new Limits($info['args'], $max))
                ->atMethod($info['fullName'])
                ->atClass($info['class'])
                ->build(),
        ];
    }

    /**
     * @psalm-param array{fullName: string, class: string|null} $info
     *
     * @psalm-return list<Violation>
     */
    private function checkComplexity(string $file, array $info, int $complexity): array
    {
        $max = $this->config->getRule('max_cyclomatic_complexity');
        if ($max <= 0 || $complexity <= $max) {
            return [];
        }

        return [
            ViolationBuilder::of('max_cyclomatic_complexity', $file, new Limits($complexity, $max))
                ->atMethod($info['fullName'])
                ->atClass($info['class'])
                ->build(),
        ];
    }
}
