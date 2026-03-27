<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

final class ComplexityConfig extends RuleSectionConfig
{
    public function maxCyclomaticComplexity(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_cyclomatic_complexity');

        $this->entries['max_cyclomatic_complexity'] = $value;

        return $this;
    }

    public function maxControlNesting(int $value): self
    {
        ConfigAssert::nonNegativeInt($value, 'max_control_nesting');

        $this->entries['max_control_nesting'] = $value;

        return $this;
    }

    public function maxWeightedCognitiveDensity(int|float $value): self
    {
        ConfigAssert::nonNegativeNumber($value, 'max_weighted_cognitive_density');

        $this->entries['max_weighted_cognitive_density'] = $value;

        return $this;
    }

    public function maxLogicalComplexityFactor(int|float $value): self
    {
        ConfigAssert::nonNegativeNumber($value, 'max_logical_complexity_factor');

        $this->entries['max_logical_complexity_factor'] = $value;

        return $this;
    }

    public function maxReturnStatements(int $value): self
    {
        ConfigAssert::nonNegativeInt($value, 'max_return_statements');

        $this->entries['max_return_statements'] = $value;

        return $this;
    }

    public function maxBooleanConditions(int $value): self
    {
        ConfigAssert::nonNegativeInt($value, 'max_boolean_conditions');

        $this->entries['max_boolean_conditions'] = $value;

        return $this;
    }

    public function maxTryCatchBlocks(int $value): self
    {
        ConfigAssert::nonNegativeInt($value, 'max_try_catch_blocks');

        $this->entries['max_try_catch_blocks'] = $value;

        return $this;
    }
}
