<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use function is_numeric;

use LogicException;

use function sprintf;
use function var_export;

/**
 * @internal
 */
final class BreathingRuleStatementPhpEmitter
{
    /** @var array<string, string> */
    private const array FLOAT_METHODS = [
        'min_visual_breathing_index'       => 'minVisualBreathingIndex',
        'min_code_oxygen_level'            => 'minCodeOxygenLevel',
        'min_identifier_readability_score' => 'minIdentifierReadabilityScore',
        'min_code_breathing_score'         => 'minCodeBreathingScore',
    ];

    public static function emit(string $ruleId, mixed $value): string
    {
        if (! is_numeric($value)) {
            throw new LogicException(sprintf('Breathing rule "%s" expects a number.', $ruleId));
        }

        $method = self::FLOAT_METHODS[$ruleId] ?? null;

        if ($method === null) {
            throw new LogicException('Unknown breathing rule: '.$ruleId);
        }

        return sprintf('$config->breathing()->%s(%s);', $method, var_export((float) $value + 0.0, true));
    }
}
