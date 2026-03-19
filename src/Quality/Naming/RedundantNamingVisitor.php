<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Naming;

use function array_key_last;
use function array_pop;

use Bunnivo\Soda\Visitor\NullableReturnVisitor;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\UnionType;

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
     *   methods: list<array{name: string, methodName: string, class: string|null, firstParamType: string|null, returnType: string|null, line: int, isPublic: bool}>
     * }
     */
    private array $result = [
        'classes' => [],
        'methods' => [],
    ];

    protected function doEnterNode(Node $node): void
    {
        match (true) {
            $node instanceof Class_, $node instanceof Trait_ => $this->enterClass($node),
            $node instanceof ClassMethod => $this->enterMethod($node),
            $node instanceof Function_   => $this->enterFunction($node),
            default                      => null,
        };
    }

    protected function doLeaveNode(Node $node): void
    {
        if (($node instanceof Class_ || $node instanceof Trait_) && $this->classStack !== []) {
            array_pop($this->classStack);
        }
    }

    private function enterClass(Class_|Trait_ $node): void
    {
        $name = $node->namespacedName?->toString();
        if ($name === null || $name === '') {
            return;
        }

        $this->classStack[] = $name;
        $this->result['classes'][] = [
            'class' => $name,
            'line'  => $node->getStartLine(),
        ];
    }

    private function enterMethod(ClassMethod $node): void
    {
        if ($node->getAttribute('parent') instanceof Interface_) {
            return;
        }

        $methodName = $node->name->toString();
        if (in_array($methodName, self::MAGIC_METHODS, true)) {
            return;
        }

        $class = $this->classStack !== [] ? $this->classStack[array_key_last($this->classStack)] : null;
        $fullName = $class !== null ? $class.'::'.$methodName : $methodName;

        $firstParamType = null;
        if ($node->params !== [] && $node->params[0]->type !== null) {
            $firstParamType = self::typeToString($node->params[0]->type);
        }

        $returnType = $node->getReturnType() !== null
            ? self::typeToString($node->getReturnType())
            : null;

        $this->result['methods'][] = [
            'name'           => $fullName,
            'methodName'     => $methodName,
            'class'          => $class,
            'firstParamType' => $firstParamType,
            'returnType'     => $returnType,
            'line'           => $node->getStartLine(),
            'isPublic'       => $node->isPublic(),
        ];
    }

    private function enterFunction(Function_ $node): void
    {
        $name = $node->namespacedName?->toString();
        if ($name === null || $name === '') {
            return;
        }

        $firstParamType = null;
        if ($node->params !== [] && $node->params[0]->type !== null) {
            $firstParamType = self::typeToString($node->params[0]->type);
        }

        $returnType = $node->getReturnType() !== null
            ? self::typeToString($node->getReturnType())
            : null;

        $this->result['methods'][] = [
            'name'           => $name,
            'methodName'     => $name,
            'class'          => null,
            'firstParamType' => $firstParamType,
            'returnType'     => $returnType,
            'line'           => $node->getStartLine(),
            'isPublic'       => true,
        ];
    }

    private static function typeToString(Node $type): string
    {
        return match (true) {
            $type instanceof Name                                => $type->toString(),
            $type instanceof Identifier                          => $type->name,
            $type instanceof NullableType                        => self::typeToString($type->type),
            $type instanceof UnionType && isset($type->types[0]) => self::typeToString($type->types[0]),
            default                                              => '',
        };
    }

    /**
     * @psalm-return array{
     *   classes: list<array{class: string, line: int}>,
     *   methods: list<array{name: string, methodName: string, class: string|null, firstParamType: string|null, returnType: string|null, line: int, isPublic: bool}>
     * }
     */
    public function result(): array
    {
        return $this->result;
    }
}
