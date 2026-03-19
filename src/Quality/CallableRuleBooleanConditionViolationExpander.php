<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

/**
 * @internal
 *
 * @psalm-param array{
 *     fluent: RuleChecker,
 *     file: string,
 *     callableInfo: array{fullName: string, class: string|null},
 *     conditions: list<array{line: int, count: int}>,
 *     max: int
 * } $params
 *
 * @return list<Violation>
 */
final class CallableRuleBooleanConditionViolationExpander
{
    public static function expand(array $params): array
    {
        $whenExceeded = $params['fluent'];

        $file = $params['file'];

        $callableInfo = $params['callableInfo'];

        $conditions = $params['conditions'];

        $max = $params['max'];

        $violations = [];
        foreach ($conditions as $cond) {
            if ($cond['count'] <= $max) {
                continue;
            }

            foreach ($whenExceeded
                ->file($file)
                ->method($callableInfo['fullName'])
                ->class($callableInfo['class'])
                ->line($cond['line'])
                ->forValue($cond['count'])
                ->limit($max)
                ->result() as $v) {
                $violations[] = $v;
            }
        }

        return $violations;
    }
}
