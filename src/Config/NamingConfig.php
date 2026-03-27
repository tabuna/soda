<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

final class NamingConfig extends RuleSectionConfig
{
    public function avoidRedundantNaming(int|float $value): self
    {
        ConfigAssert::nonNegativeNumber($value, 'avoid_redundant_naming');

        $this->entries['avoid_redundant_naming'] = $value;

        return $this;
    }

    /**
     * @param list<string> $methodExceptions
     */
    public function booleanMethodsWithoutPrefix(int $threshold, array $methodExceptions = []): self
    {
        ConfigAssert::nonNegativeInt($threshold, 'boolean_methods_without_prefix.threshold');
        ConfigAssert::nonEmptyMethodNames($methodExceptions, 'boolean_methods_without_prefix.exceptions.methods');

        $payload = ['threshold' => $threshold];

        if ($methodExceptions !== []) {
            $payload['exceptions'] = [
                'methods' => array_values($methodExceptions),
            ];
        }

        $this->entries['boolean_methods_without_prefix'] = $payload;

        return $this;
    }

    /**
     * @param array<string, mixed> $payload threshold / exceptions / options (legacy JSON block shape)
     */
    public function importBooleanMethodsRule(array $payload): self
    {
        $this->entries['boolean_methods_without_prefix'] = $payload;

        return $this;
    }
}
