<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Breathing;

use PhpParser\Node;
use PhpParser\NodeTraverser;

use function token_get_all;

final class BreathingAnalyser
{
    /**
     * @param array<array-key, Node>|null $astNodes
     */
    public static function analyse(string $source, ?array $astNodes = null): BreathingMetrics
    {
        $tokens = token_get_all($source);
        $lineData = LineAnalyser::analyse($source);
        $lineBlock = new LineBlockData($lineData);

        $wcd = self::computeWcd($tokens, $lineBlock);
        $irs = IrsCalculator::calculate($tokens);
        $vbi = VbiCalculator::calculate($lineBlock);
        $col = ColCalculator::calculate($lineBlock);
        $lcf = self::resolveLcf($astNodes);

        $factors = new BreathingFactors(
            new CognitiveLoad($wcd, $lcf),
            new AirinessFactors($vbi, $irs, $col),
        );
        $cbs = self::computeCbs($factors, $lineBlock);

        return BreathingMetrics::fromFactors($factors, $cbs);
    }

    /**
     * @param list<string|array{0: int, 1: string, 2: int}> $tokens
     */
    private static function computeWcd(array $tokens, LineBlockData $lineBlock): float
    {
        $resolver = new TokenWeightResolver();

        return WcdCalculator::calculate($tokens, $lineBlock->nLines(), $resolver);
    }

    /**
     * @param array<array-key, Node>|null $astNodes
     */
    private static function resolveLcf(?array $astNodes): float
    {
        if ($astNodes === null) {
            return 1.0;
        }

        $nodes = array_values($astNodes);

        return self::lcfFromAst($nodes);
    }

    private static function computeCbs(BreathingFactors $factors, LineBlockData $lineBlock): float
    {
        $input = CbsInput::fromFactors(
            $factors->cognitive(),
            $factors->airiness(),
            $lineBlock->totalLines(),
        );

        return CbsCalculator::calculate($input);
    }

    /**
     * @param array<int, Node> $nodes
     */
    private static function lcfFromAst(array $nodes): float
    {
        $visitor = new LcfVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        return $visitor->lcf();
    }
}
