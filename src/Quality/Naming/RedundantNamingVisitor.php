<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Naming;

use function array_key_last;
use function array_pop;
use function array_values;

use Bunnivo\Soda\Visitor\NullableReturnVisitor;

use function in_array;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;

/**
 * Collects class and method names with param/return types for AvoidRedundantNaming rule.
 *
 * @internal
 */
final class RedundantNamingVisitor extends NullableReturnVisitor
{
    private const array MAGIC_METHODS = [
        '__construct', '__destruct', '__call', '__callStatic', '__get', '__set',
        '__isset', '__unset', '__sleep', '__wakeup', '__serialize', '__unserialize',
        '__toString', '__invoke', '__set_state', '__clone', '__debugInfo',
    ];

    /**
     * @psalm-var list<non-empty-string>
     */
    private array $classStack = [];

    /**
     * @psalm-var array{
     *   classes: list<array{class: string, line: int}>,
     *   methods: list<array{name: string, methodName: string, class: string|null, firstParamType: string|null, returnType: string|null, line: int, isPublic: bool, hasOverrideAttribute: bool}>,
     *   types: list<array{name: string, kind: 'class'|'trait'|'interface', line: int, inherits: list<string>, methods: list<string>}>
     * }
     */
    private array $result = [
        'classes' => [],
        'methods' => [],
        'types'   => [],
    ];

    /**
     * @psalm-var array<string, array{name: string, kind: 'class'|'trait'|'interface', line: int, inherits: list<string>, methods: list<string>}>
     */
    private array $types = [];

    protected function doEnterNode(Node $node): void
    {
        if ($node instanceof Class_ || $node instanceof Trait_ || $node instanceof Interface_) {
            $this->enterType($node);

            return;
        }

        if ($node instanceof ClassMethod) {
            $this->enterMethod($node);

            return;
        }

        if ($node instanceof Function_) {
            $this->enterFunction($node);
        }
    }

    protected function doLeaveNode(Node $node): void
    {
        if (($node instanceof Class_ || $node instanceof Trait_ || $node instanceof Interface_) && $this->classStack !== []) {
            array_pop($this->classStack);
        }
    }

    private function enterType(Class_|Trait_|Interface_ $node): void
    {
        $name = $node->namespacedName?->toString();
        if ($name === null || $name === '') {
            return;
        }

        $this->classStack[] = $name;

        $this->types[$name] = [
            'name'     => $name,
            'kind'     => $this->kindOf($node),
            'line'     => $node->getStartLine(),
            'inherits' => $this->inheritsOf($node),
            'methods'  => [],
        ];

        if (! $node instanceof Interface_) {
            $this->result['classes'][] = [
                'class' => $name,
                'line'  => $node->getStartLine(),
            ];
        }
    }

    private function enterMethod(ClassMethod $node): void
    {
        $methodName = $node->name->toString();
        if (in_array($methodName, self::MAGIC_METHODS, true)) {
            return;
        }

        $class = $this->currentClassName();
        if ($class !== null && isset($this->types[$class])) {
            $this->types[$class]['methods'][] = $methodName;
        }

        if ($node->getAttribute('parent') instanceof Interface_) {
            return;
        }

        $this->result['methods'][] = RedundantNamingMethodResultFactory::fromClassMethod($node, $class);
    }

    private function enterFunction(Function_ $node): void
    {
        $methodResult = RedundantNamingMethodResultFactory::fromFunction($node);

        if ($methodResult === null) {
            return;
        }

        $this->result['methods'][] = $methodResult;
    }

    /**
     * @psalm-return array{
     *   classes: list<array{class: string, line: int}>,
     *   methods: list<array{name: string, methodName: string, class: string|null, firstParamType: string|null, returnType: string|null, line: int, isPublic: bool, hasOverrideAttribute: bool}>,
     *   types: list<array{name: string, kind: 'class'|'trait'|'interface', line: int, inherits: list<string>, methods: list<string>}>
     * }
     */
    public function result(): array
    {
        $this->result['types'] = array_values($this->types);

        return $this->result;
    }

    private function currentClassName(): ?string
    {
        return $this->classStack !== [] ? $this->classStack[array_key_last($this->classStack)] : null;
    }

    /**
     * @return 'class'|'trait'|'interface'
     */
    private function kindOf(Class_|Trait_|Interface_ $node): string
    {
        return match (true) {
            $node instanceof Interface_ => 'interface',
            $node instanceof Trait_     => 'trait',
            default                     => 'class',
        };
    }

    /**
     * @return list<string>
     */
    private function inheritsOf(Class_|Trait_|Interface_ $node): array
    {
        return match (true) {
            $node instanceof Class_ => array_values(array_filter(
                [
                    $node->extends?->toString(),
                    ...array_map(static fn (Node\Name $name): string => $name->toString(), $node->implements),
                ],
                static fn (mixed $name): bool => is_string($name) && $name !== '',
            )),
            $node instanceof Interface_ => array_map(static fn (Node\Name $name): string => $name->toString(), $node->extends),
            default                     => [],
        };
    }
}
