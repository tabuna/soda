<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Breathing\BreathingAnalyser;
use Bunnivo\Soda\Breathing\BreathingMetrics;
use Bunnivo\Soda\Complexity\EnumAwareComplexityVisitor;

use function file_get_contents;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use SebastianBergmann\Complexity\ComplexityCollection;
use SebastianBergmann\LinesOfCode\LineCountingVisitor;
use SebastianBergmann\LinesOfCode\LinesOfCode;

use function sprintf;
use function substr_count;

/**
 * @internal
 */
final class FileAnalyser
{
    /**
     * @psalm-param non-empty-string $file
     *
     * @throws ParserException
     *
     * @psalm-return array{complexity: ComplexityCollection, linesOfCode: LinesOfCode, structure: array<string, mixed>, breathing: BreathingMetrics}
     */
    public function analyse(string $file): array
    {
        $source = file_get_contents($file);

        throw_if($source === false, ParserException::class, 'Cannot read '.$file, 0);

        $lines = substr_count($source, "\n");
        if ($lines === 0 && $source !== '') {
            $lines = 1;
        }

        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        try {
            $nodes = $parser->parse($source);
        } catch (Error $error) {
            throw new ParserException(
                sprintf('Cannot parse %s: %s', $file, $error->getMessage()),
                $error->getCode(),
                $error,
            );
        }

        throw_if($nodes === null, ParserException::class, 'Cannot parse '.$file, 0);

        $traverser = new NodeTraverser;
        $complexityVisitor = new EnumAwareComplexityVisitor(false);
        $lineCountingVisitor = new LineCountingVisitor(max(0, $lines));
        $structureVisitor = new Structure\MetricsVisitor();

        $traverser->addVisitor(new NameResolver);
        $traverser->addVisitor(new ParentConnectingVisitor);
        $traverser->addVisitor($complexityVisitor);
        $traverser->addVisitor($lineCountingVisitor);

        $traverser->addVisitor($structureVisitor);
        $traverser->traverse($nodes);

        $breathing = BreathingAnalyser::analyse($source, $nodes);

        return [
            'complexity'  => $complexityVisitor->result(),
            'linesOfCode' => $lineCountingVisitor->result(),
            'structure'   => $structureVisitor->result(),
            'breathing'   => $breathing,
        ];
    }
}
