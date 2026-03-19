<?php

declare(strict_types=1);
/*
 * This file is part of Soda.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\EvaluateInput;
use Bunnivo\Soda\Quality\QualityConfig;
use Bunnivo\Soda\Quality\QualityEngine;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(QualityEngine::class)]
#[Small]
final class QualityEngineTest extends TestCase
{
    private function createResult(int $classesOrTraits = 1): Result
    {
        $loc = new LocMetrics([
            'directories'           => 1,
            'files'                 => 1,
            'linesOfCode'           => 100,
            'commentLinesOfCode'    => 0,
            'nonCommentLinesOfCode' => 100,
            'logicalLinesOfCode'    => 50,
        ]);
        $complexity = new ComplexityMetrics([
            'functions'       => 0,
            'funcLowest'      => 1,
            'funcAverage'     => 1.0,
            'funcHighest'     => 1,
            'classesOrTraits' => $classesOrTraits,
            'methods'         => 1,
            'methodLowest'    => 1,
            'methodAverage'   => 1.0,
            'methodHighest'   => 1,
        ]);

        return new Result([], new CoreMetrics($loc, $complexity));
    }

    /**
     * @return \Iterator<string, array{rule: string, config: array<string, int>, metrics: array<mixed>, complexity: array<string, int>, nesting?: array<string, array{depth: int, line: int, file: string}>, returns?: array<string, int>, booleanConditions?: array<string, list<array{line: int, count: int}>>, tryCatch?: array<string, int>, expectedRule: string}>
     */
    public static function ruleViolationProvider(): \Iterator
    {
        $baseClass = [
            'loc'                 => 10,
            'methods'             => 1,
            'properties'          => 1,
            'public_methods'      => 1,
            'dependencies'        => 1,
            'efferent_coupling'   => 0,
            'traits'              => 0,
            'interfaces'          => 0,
            'namespace'           => 'App',
            'namespace_depth'     => 1,
        ];
        yield 'max_method_length' => [
            'rule'    => 'max_method_length',
            'config'  => ['max_method_length' => 5],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => $baseClass],
                    'methods'       => ['App\Foo::bar' => ['loc' => 10, 'args' => 0]],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => ['App\Foo::bar' => 1],
            'nesting'      => [],
            'expectedRule' => 'max_method_length',
        ];
        yield 'max_class_length' => [
            'rule'    => 'max_class_length',
            'config'  => ['max_class_length' => 5],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => array_merge($baseClass, ['loc' => 10])],
                    'methods'       => ['App\Foo::bar' => ['loc' => 1, 'args' => 0]],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => ['App\Foo::bar' => 1],
            'nesting'      => [],
            'expectedRule' => 'max_class_length',
        ];
        yield 'max_arguments' => [
            'rule'    => 'max_arguments',
            'config'  => ['max_arguments' => 2],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => $baseClass],
                    'methods'       => ['App\Foo::bar' => ['loc' => 1, 'args' => 5]],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => ['App\Foo::bar' => 1],
            'nesting'      => [],
            'expectedRule' => 'max_arguments',
        ];
        yield 'max_methods_per_class' => [
            'rule'    => 'max_methods_per_class',
            'config'  => ['max_methods_per_class' => 2],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => array_merge($baseClass, ['methods' => 5])],
                    'methods'       => [],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => [],
            'nesting'      => [],
            'expectedRule' => 'max_methods_per_class',
        ];
        yield 'max_file_loc' => [
            'rule'    => 'max_file_loc',
            'config'  => ['max_file_loc' => 10],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 100,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => $baseClass],
                    'methods'       => [],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => [],
            'nesting'      => [],
            'expectedRule' => 'max_file_loc',
        ];
        yield 'max_cyclomatic_complexity' => [
            'rule'    => 'max_cyclomatic_complexity',
            'config'  => ['max_cyclomatic_complexity' => 2],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => $baseClass],
                    'methods'       => ['App\Foo::bar' => ['loc' => 1, 'args' => 0]],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => ['App\Foo::bar' => 10],
            'nesting'      => [],
            'expectedRule' => 'max_cyclomatic_complexity',
        ];
        yield 'max_properties_per_class' => [
            'rule'    => 'max_properties_per_class',
            'config'  => ['max_properties_per_class' => 2],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => array_merge($baseClass, ['properties' => 5])],
                    'methods'       => [],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => [],
            'nesting'      => [],
            'expectedRule' => 'max_properties_per_class',
        ];
        yield 'max_public_methods' => [
            'rule'    => 'max_public_methods',
            'config'  => ['max_public_methods' => 2],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => array_merge($baseClass, ['public_methods' => 5])],
                    'methods'       => [],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => [],
            'nesting'      => [],
            'expectedRule' => 'max_public_methods',
        ];
        yield 'max_dependencies' => [
            'rule'    => 'max_dependencies',
            'config'  => ['max_dependencies' => 2],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => array_merge($baseClass, ['dependencies' => 5])],
                    'methods'       => [],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => [],
            'nesting'      => [],
            'expectedRule' => 'max_dependencies',
        ];
        yield 'max_efferent_coupling' => [
            'rule'    => 'max_efferent_coupling',
            'config'  => ['max_efferent_coupling' => 10],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => array_merge($baseClass, ['efferent_coupling' => 11])],
                    'methods'       => [],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => [],
            'nesting'      => [],
            'expectedRule' => 'max_efferent_coupling',
        ];
        yield 'max_classes_per_file' => [
            'rule'    => 'max_classes_per_file',
            'config'  => ['max_classes_per_file' => 1],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 3,
                    'classes'       => [
                        'App\Foo' => $baseClass,
                        'App\Bar' => $baseClass,
                        'App\Baz' => $baseClass,
                    ],
                    'methods'    => [],
                    'namespaces' => ['App' => 3],
                ],
            ],
            'complexity'   => [],
            'nesting'      => [],
            'expectedRule' => 'max_classes_per_file',
        ];
        yield 'max_namespace_depth' => [
            'rule'    => 'max_namespace_depth',
            'config'  => ['max_namespace_depth' => 2],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\A\B\C\Foo' => array_merge($baseClass, [
                        'namespace'       => 'App\A\B\C',
                        'namespace_depth' => 4,
                    ])],
                    'methods'    => [],
                    'namespaces' => ['App\A\B\C' => 1],
                ],
            ],
            'complexity'   => [],
            'nesting'      => [],
            'expectedRule' => 'max_namespace_depth',
        ];
        yield 'max_classes_per_namespace' => [
            'rule'    => 'max_classes_per_namespace',
            'config'  => ['max_classes_per_namespace' => 2],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 5,
                    'classes'       => [
                        'App\Foo'  => array_merge($baseClass, ['namespace' => 'App', 'namespace_depth' => 1]),
                        'App\Bar'  => array_merge($baseClass, ['namespace' => 'App', 'namespace_depth' => 1]),
                        'App\Baz'  => array_merge($baseClass, ['namespace' => 'App', 'namespace_depth' => 1]),
                        'App\Qux'  => array_merge($baseClass, ['namespace' => 'App', 'namespace_depth' => 1]),
                        'App\Quux' => array_merge($baseClass, ['namespace' => 'App', 'namespace_depth' => 1]),
                    ],
                    'methods'    => [],
                    'namespaces' => ['App' => 5],
                ],
            ],
            'complexity'   => [],
            'nesting'      => [],
            'expectedRule' => 'max_classes_per_namespace',
        ];
        yield 'max_traits_per_class' => [
            'rule'    => 'max_traits_per_class',
            'config'  => ['max_traits_per_class' => 1],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => array_merge($baseClass, ['traits' => 4])],
                    'methods'       => [],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => [],
            'nesting'      => [],
            'expectedRule' => 'max_traits_per_class',
        ];
        yield 'max_interfaces_per_class' => [
            'rule'    => 'max_interfaces_per_class',
            'config'  => ['max_interfaces_per_class' => 1],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => array_merge($baseClass, ['interfaces' => 3])],
                    'methods'       => [],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => [],
            'nesting'      => [],
            'expectedRule' => 'max_interfaces_per_class',
        ];
        yield 'max_classes_per_project' => [
            'rule'    => 'max_classes_per_project',
            'config'  => ['max_classes_per_project' => 5],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => $baseClass],
                    'methods'       => [],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => [],
            'nesting'      => [],
            'expectedRule' => 'max_classes_per_project',
        ];
        yield 'max_control_nesting' => [
            'rule'    => 'max_control_nesting',
            'config'  => ['max_control_nesting' => 3],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => $baseClass],
                    'methods'       => ['App\Foo::bar' => ['loc' => 1, 'args' => 0]],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => ['App\Foo::bar' => 1],
            'nesting'      => ['App\Foo::bar' => ['depth' => 4, 'line' => 42, 'file' => '/file.php']],
            'expectedRule' => 'max_control_nesting',
        ];
        yield 'max_return_statements' => [
            'rule'    => 'max_return_statements',
            'config'  => ['max_return_statements' => 4],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => $baseClass],
                    'methods'       => ['App\Foo::bar' => ['loc' => 1, 'args' => 0]],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => ['App\Foo::bar' => 1],
            'nesting'      => [],
            'returns'      => ['App\Foo::bar' => 5],
            'expectedRule' => 'max_return_statements',
        ];
        yield 'max_boolean_conditions' => [
            'rule'    => 'max_boolean_conditions',
            'config'  => ['max_boolean_conditions' => 3],
            'metrics' => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => $baseClass],
                    'methods'       => ['App\Foo::bar' => ['loc' => 1, 'args' => 0]],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'        => ['App\Foo::bar' => 1],
            'nesting'           => [],
            'booleanConditions' => ['App\Foo::bar' => [['line' => 10, 'count' => 5]]],
            'expectedRule'      => 'max_boolean_conditions',
        ];
        yield 'max_try_catch_blocks' => [
            'rule'         => 'max_try_catch_blocks',
            'config'       => ['max_try_catch_blocks' => 2],
            'metrics'      => [
                '/file.php' => [
                    'file_loc'      => 50,
                    'classes_count' => 1,
                    'classes'       => ['App\Foo' => $baseClass],
                    'methods'       => ['App\Foo::run' => ['loc' => 1, 'args' => 0]],
                    'namespaces'    => ['App' => 1],
                ],
            ],
            'complexity'   => ['App\Foo::run' => 1],
            'nesting'      => [],
            'expectedRule' => 'max_try_catch_blocks',
            'tryCatch'     => ['App\Foo::run' => 3],
        ];
    }

    #[DataProvider('ruleViolationProvider')]
    public function testEachRuleProducesViolationWhenExceeded(
        string $rule,
        array $config,
        array $metrics,
        array $complexity,
        array $nesting,
        string $expectedRule,
        array $returns = [],
        array $booleanConditions = [],
        array $tryCatch = [],
    ): void {
        $configObj = new QualityConfig($config);
        $engine = QualityEngine::create($configObj);

        $classesOrTraits = $rule === 'max_classes_per_project' ? 10 : 1;
        $input = EvaluateInput::fromArrays($metrics, [
            'complexity' => $complexity,
            'nesting'    => $nesting,
            'returns'    => $returns,
            'conditions' => $booleanConditions,
            'tryCatch'   => $tryCatch,
        ]);
        $result = $engine->evaluate($this->createResult($classesOrTraits), $input);

        $rules = $result->violations->pluck('rule')->all();
        $this->assertContains($expectedRule, $rules, sprintf('Rule %s should produce violation', $expectedRule));
    }

    public function testDisabledRuleProducesNoViolation(): void
    {
        $config = new QualityConfig([]); // no rules
        $engine = QualityEngine::create($config);

        $metrics = [
            '/file.php' => [
                'file_loc'      => 10000,
                'classes_count' => 100,
                'classes'       => [
                    'App\Foo' => [
                        'loc'                 => 1000,
                        'methods'             => 50,
                        'properties'          => 50,
                        'public_methods'      => 50,
                        'dependencies'        => 50,
                        'efferent_coupling'   => 50,
                        'traits'              => 10,
                        'interfaces'          => 10,
                        'namespace'           => 'App',
                        'namespace_depth'     => 10,
                    ],
                ],
                'methods'    => ['App\Foo::bar' => ['loc' => 100, 'args' => 20]],
                'namespaces' => ['App' => 1],
            ],
        ];

        $input = EvaluateInput::fromArrays($metrics, ['complexity' => ['App\Foo::bar' => 100]]);
        $result = $engine->evaluate($this->createResult(5000), $input);

        $this->assertCount(0, $result->violations);
    }
}
