<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

final class BreathingConfig extends RuleSectionConfig
{
    public function minVisualBreathingIndex(int|float $value): self
    {
        ConfigAssert::nonNegativeNumber($value, 'min_visual_breathing_index');

        $this->entries['min_visual_breathing_index'] = $value;

        return $this;
    }

    public function minCodeOxygenLevel(int|float $value): self
    {
        ConfigAssert::nonNegativeNumber($value, 'min_code_oxygen_level');

        $this->entries['min_code_oxygen_level'] = $value;

        return $this;
    }

    public function minIdentifierReadabilityScore(int|float $value): self
    {
        ConfigAssert::nonNegativeNumber($value, 'min_identifier_readability_score');

        $this->entries['min_identifier_readability_score'] = $value;

        return $this;
    }

    public function minCodeBreathingScore(int|float $value): self
    {
        ConfigAssert::nonNegativeNumber($value, 'min_code_breathing_score');

        $this->entries['min_code_breathing_score'] = $value;

        return $this;
    }
}
