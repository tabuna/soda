<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use function array_pop;
use function count;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;

/**
 * Collects per-method, per-class, per-file metrics for quality analysis.
 *
 * @internal
 */
final class QualityMetricsVisitor extends NodeVisitorAbstract
{
    /**
     * @psalm-var array{
     *   file_loc: int,
     *   classes_count: int,
     *   classes: array<string, array{
     *     loc: int,
     *     methods: int,
     *     properties: int,
     *     public_methods: int,
     *     dependencies: int,
     *     traits: int,
     *     interfaces: int,
     *     namespace: string,
     *     namespace_depth: int
     *   }>,
     *   methods: array<string, array{loc: int, args: int}>,
     *   namespaces: array<string, int>
     * }
     */
    private array $result = [
        'file_loc'      => 0,
        'classes_count' => 0,
        'classes'       => [],
        'methods'       => [],
        'namespaces'    => [],
    ];

    /**
     * @psalm-var non-negative-int
     */
    private int $fileLines;

    /**
     * @psalm-var list<non-empty-string>
     */
    private array $classStack = [];

    /**
     * @psalm-param non-negative-int $fileLines
     */
    public function __construct(int $fileLines)
    {
        $this->fileLines = $fileLines;
    }

    #[\Override]
    public function enterNode(Node $node): void
    {
        if ($node instanceof Class_ || $node instanceof Trait_) {
            $this->handleClassOrTrait($node);

            return;
        }

        if ($node instanceof ClassMethod) {
            $this->handleClassMethod($node);

            return;
        }

        if ($node instanceof Function_) {
            $this->handleFunction($node);
        }
    }

    private function handleClassOrTrait(Class_|Trait_ $node): void
    {
        if ($node->getAttribute('parent') instanceof New_) {
            return;
        }

        $name = $node->namespacedName?->toString();
        if ($name === null || $name === '') {
            return;
        }

        $this->classStack[] = $name;
        $this->result['classes_count']++;
        $this->result['classes'][$name] = MetricsExtractor::extract($node);
        $this->updateNamespace($name);
    }

    private function updateNamespace(string $name): void
    {
        $namespace = $this->result['classes'][$name]['namespace'] ?? '';
        if ($namespace !== '') {
            $this->result['namespaces'][$namespace] = ($this->result['namespaces'][$namespace] ?? 0) + 1;
        }
    }

    private function handleClassMethod(ClassMethod $node): void
    {
        if ($node->getAttribute('parent') instanceof Interface_) {
            return;
        }
        if ($node->isAbstract()) {
            return;
        }

        $class = $this->classStack !== [] ? $this->classStack[array_key_last($this->classStack)] : 'unknown';
        $name = $class.'::'.$node->name->toString();
        $loc = $node->getEndLine() - $node->getStartLine() + 1;

        $this->result['methods'][$name] = ['loc' => $loc, 'args' => count($node->params)];
        if (isset($this->result['classes'][$class])) {
            $this->result['classes'][$class]['methods']++;
        }
    }

    private function handleFunction(Function_ $node): void
    {
        $name = $node->namespacedName?->toString();
        if ($name === null || $name === '') {
            return;
        }
        $loc = $node->getEndLine() - $node->getStartLine() + 1;
        $this->result['methods'][$name] = ['loc' => $loc, 'args' => count($node->params)];
    }

    #[\Override]
    public function leaveNode(Node $node): void
    {
        if ($node instanceof Class_ || $node instanceof Trait_) {
            if ($this->classStack !== []) {
                array_pop($this->classStack);
            }
        }
    }

    /**
     * @psalm-return array{
     *   file_loc: int,
     *   classes_count: int,
     *   classes: array<string, array{loc: int, methods: int, properties: int, public_methods: int, dependencies: int, traits: int, interfaces: int, namespace: string, namespace_depth: int}>,
     *   methods: array<string, array{loc: int, args: int}>,
     *   namespaces: array<string, int>
     * }
     */
    public function result(): array
    {
        $this->result['file_loc'] = $this->fileLines;

        return $this->result;
    }
}
