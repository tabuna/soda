<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\Engine\QualityEngineNamespaceAggregator;
use PHPUnit\Framework\TestCase;

final class QualityEngineNamespaceAggregatorTest extends TestCase
{
    public function testAggregatesCountsAcrossFilesForSameNamespace(): void
    {
        $aggregated = QualityEngineNamespaceAggregator::aggregate([
            '/src/One.php' => [
                'namespaces' => [
                    'App\Services' => 2,
                ],
            ],
            '/src/Two.php' => [
                'namespaces' => [
                    'App\Services' => 3,
                ],
            ],
            '/src/Three.php' => [
                'namespaces' => [
                    'App\Http' => 1,
                ],
            ],
        ]);

        $this->assertSame(
            [
                'count' => 5,
                'file'  => '/src/One.php',
            ],
            $aggregated->get('App\Services'),
        );
        $this->assertSame(
            [
                'count' => 1,
                'file'  => '/src/Three.php',
            ],
            $aggregated->get('App\Http'),
        );
    }
}
