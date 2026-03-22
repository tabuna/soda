<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\RuleNaming;

use function array_fill_keys;
use function array_values;

use Bunnivo\Soda\Quality\EvaluationContext;

use function is_array;
use function is_string;

/**
 * @internal
 */
final class BooleanMethodTypeIndexBuilder
{
    /**
     * @return array<string, array{inherits: list<string>, methods: array<string, true>}>
     */
    public static function build(EvaluationContext $context): array
    {
        $index = [];

        foreach ($context->fileMetrics->qualityMetrics() as $metrics) {
            $naming = $metrics['naming'] ?? null;
            $types = is_array($naming) ? ($naming['types'] ?? null) : null;

            if (! is_array($types)) {
                continue;
            }

            foreach ($types as $typeData) {
                $entry = self::typeIndexEntry($typeData);

                if ($entry === null) {
                    continue;
                }

                $index[$entry['name']] = $entry['data'];
            }
        }

        return $index;
    }

    /**
     * @return array{name: string, data: array{inherits: list<string>, methods: array<string, true>}}|null
     */
    private static function typeIndexEntry(mixed $typeData): ?array
    {
        if (! is_array($typeData)) {
            return null;
        }

        $name = $typeData['name'] ?? null;

        if (! is_string($name)) {
            return null;
        }

        return [
            'name' => $name,
            'data' => [
                'inherits' => self::stringList($typeData['inherits'] ?? []),
                'methods'  => array_fill_keys(self::stringList($typeData['methods'] ?? []), true),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private static function stringList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(
            $values,
            static fn (mixed $name): bool => is_string($name) && $name !== '',
        ));
    }
}
