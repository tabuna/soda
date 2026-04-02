<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Verification;

use Bunnivo\Soda\Breathing\BreathingAnalyser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Performance verification: 10k lines < 2 seconds.
 *
 * Note: 100k lines test requires memory_limit >= 256M; run separately:
 *   php -d memory_limit=512M vendor/bin/phpunit --group performance
 */
#[Group('verification')]
#[Group('performance')]
final class BreathingPerformanceTest extends TestCase
{
    public function test10kLinesUnder2Seconds(): void
    {
        $line = "<?php\n\$x = 1;\n\$y = 2;\nreturn \$x + \$y;\n";
        $lines = 10_000 / 4;
        $code = str_repeat($line, $lines);

        $start = microtime(true);
        $metrics = BreathingAnalyser::analyse($code);
        $elapsed = microtime(true) - $start;

        $this->assertGreaterThanOrEqual(0, $metrics->cbs());
        $this->assertLessThan(2.0, $elapsed, '10k lines must be analysed in < 2 seconds');
    }
}
