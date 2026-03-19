<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

use PhpParser\Node;

final class BreathingAnalyser
{
    /**
     * @param array<array-key, Node>|null $astNodes
     */
    public static function analyse(string $source, ?array $astNodes = null): BreathingMetrics
    {
        $prepared = BreathingSourceTokenPipeline::prepare($source);

        $tokens = $prepared['tokens'];

        $lineBlock = $prepared['lineBlock'];

        $perceptual = BreathingPerceptualIndices::collect($tokens, $lineBlock);

        $wcd = BreathingWcdResolver::resolve($tokens, $lineBlock);

        $lcf = BreathingLcfFromAstResolver::resolve($astNodes);

        $factors = new BreathingFactors(
            new CognitiveLoad($wcd, $lcf),
            new AirinessFactors($perceptual['vbi'], $perceptual['irs'], $perceptual['col']),
        );

        $cbs = BreathingCbsResolver::resolve($factors, $lineBlock);

        return BreathingMetrics::fromFactors($factors, $cbs);
    }
}
