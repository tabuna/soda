<?php

declare(strict_types=1);
use Bunnivo\Soda\Breathing\BreathingAnalyser;
use PhpParser\ParserFactory;

/**
 * Debug script: manual verification of CBS metrics.
 *
 * Usage: php scripts/debug-breathing.php src/Quality/Rule/ClassesChecker.php
 */

require __DIR__.'/../vendor/autoload.php';

$file = $argv[1] ?? __DIR__.'/../src/Quality/Rule/ClassesChecker.php';
$source = file_get_contents($file);

$parser = (new ParserFactory())->createForNewestSupportedVersion();
$nodes = $parser->parse($source);

$metrics = BreathingAnalyser::analyse($source, $nodes);

echo '=== Breathing Metrics Debug: '.basename($file)." ===\n\n";
echo 'WCD:  '.$metrics->wcd()."\n";
echo 'LCF:  '.$metrics->lcf()."\n";
echo 'VBI:  '.$metrics->vbi()."\n";
echo 'IRS:  '.$metrics->irs()."\n";
echo 'COL:  '.$metrics->col()."\n";
echo 'CBS:  '.$metrics->cbs()."\n\n";

// Manual line analysis
$lines = explode("\n", $source);
$nBlank = 0;
$blocks = [];
$currentBlock = 0;

foreach ($lines as $i => $line) {
    $trimmed = trim($line);
    if ($trimmed === '') {
        $nBlank++;
        if ($currentBlock > 0) {
            $blocks[] = $currentBlock;
            $currentBlock = 0;
        }
    } else {
        $currentBlock++;
    }
}
if ($currentBlock > 0) {
    $blocks[] = $currentBlock;
}

$nLines = count(array_filter($lines, fn ($l) => trim($l) !== ''));
$shortBlocks = count(array_filter($blocks, fn ($b) => $b <= 3));

echo "=== Line Analysis ===\n";
echo 'Total lines: '.count($lines)."\n";
echo "Non-empty (nLines): $nLines\n";
echo "Blank (nBlank): $nBlank\n";
echo 'Blocks: '.implode(', ', $blocks)."\n";
echo "Short blocks (≤3): $shortBlocks\n\n";

// VBI components
$ratio = $nLines > 0 ? $nBlank / $nLines : 0;
$maxBlock = $blocks !== [] ? max($blocks) : 0;
$sigma = stddev($blocks);
$blockFactor = $maxBlock > 0 ? 1 - ($sigma / $maxBlock) : 1.0;
$vbiManual = $nLines > 0 && $blocks !== [] ? $ratio * max(0, $blockFactor) : 0;

echo "=== VBI Manual ===\n";
echo "ratio (nBlank/nLines): $ratio\n";
echo "maxBlock: $maxBlock\n";
echo "sigma: $sigma\n";
echo "blockFactor (1 - sigma/max): $blockFactor\n";
echo "VBI = ratio * blockFactor: $vbiManual\n";
echo 'Tool VBI: '.$metrics->vbi()."\n\n";

// COL manual (base; tool adds declarative bonus)
$colBase = $nLines > 0 ? ($nBlank + $shortBlocks) / $nLines : 0;
echo "=== COL ===\n";
echo "COL base = (nBlank + shortBlocks) / nLines = ($nBlank + $shortBlocks) / $nLines = $colBase\n";
echo 'Tool COL (with declarative bonus, cap 0.65): '.$metrics->col()."\n\n";

// CBS formula: divisor = 100+100/(1+n/25), sizeFactor = max(1, min(10, 600/(n+40)))
$totalLines = count($lines);
$divisor = 100 + 120 / (1 + $totalLines / 25);
if ($totalLines > 400) {
    $divisor *= 5.0;
} elseif ($totalLines < 250 && $totalLines >= 50) {
    $divisor *= 2.9;
}
$sizeFactor = max(1.0, min(10.0, 2400.0 / ($totalLines + 50)));
if ($totalLines > 400) {
    $sizeFactor = min(10.0, $sizeFactor * 2.0);
}
$num = $metrics->vbi() * $metrics->irs() * $metrics->col();
$effectiveLcf = min($metrics->lcf(), 4.0);
$denom = 1 + ($metrics->wcd() * $effectiveLcf) / $divisor;
$cbsManual = min(1.0, ($num * $sizeFactor) / $denom);
echo "=== CBS Formula ===\n";
echo "CBS = min(1, (VBI*IRS*COL)*sizeFactor / (1+WCD*min(LCF,4)/$divisor))  [totalLines=$totalLines, sizeFactor=$sizeFactor, effectiveLCF=$effectiveLcf]\n";
echo "    = min(1, ($num * $sizeFactor) / $denom) = $cbsManual\n";
echo 'Tool CBS: '.$metrics->cbs()."\n";

function stddev(array $values): float
{
    $n = count($values);
    if ($n < 2) {
        return 0.0;
    }
    $mean = array_sum($values) / $n;
    $variance = 0.0;
    foreach ($values as $v) {
        $variance += ($v - $mean) ** 2;
    }

    return ($variance / ($n - 1)) ** 0.5;
}
