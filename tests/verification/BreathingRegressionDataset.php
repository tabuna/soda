<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Verification;

/**
 * Regression dataset for Code Breathing Analyzer.
 *
 * Each entry: code => expected metrics (tolerance 0.0001).
 * Keys in expected can be partial — only listed metrics are asserted.
 *
 * @return array<string, array{code: string, expected: array<string, float>}>
 */
final class BreathingRegressionDataset
{
    /**
     * @return array<string, array{code: string, expected: array<string, float>}>
     */
    public static function cases(): array
    {
        $base = [
            'empty' => [
                'code'     => '',
                'expected' => ['wcd' => 0.0, 'vbi' => 0.0, 'col' => 0.0, 'cbs' => 0.0],
            ],
            'single_statement' => [
                'code'     => '<?php $x=1;',
                'expected' => ['vbi' => 0.0],
            ],
            'function_add' => [
                'code'     => '<?php function add($a,$b){return $a+$b;}',
                'expected' => ['lcf' => 1.0],
            ],
            'if_only' => [
                'code'     => '<?php if($a){}',
                'expected' => ['lcf' => 1.7],
            ],
            'foreach_only' => [
                'code'     => '<?php foreach($a as $x){}',
                'expected' => ['lcf' => 1.6],
            ],
            'nested_if_foreach' => [
                'code'     => "<?php\nif(\$a){\nforeach(\$b as \$x){\nif(\$x){}\n}\n}",
                'expected' => ['lcf' => 3.0],
            ],
        ];

        $generated = self::generateVariants();

        return array_merge($base, $generated);
    }

    /**
     * @return array<string, array{code: string, expected: array<string, float>}>
     */
    private static function generateVariants(): array
    {
        $cases = [];
        $templates = [
            'assign_%d'       => '<?php $v%d=1;',
            'assign_long_%d'  => '<?php $variableName%d=42;',
            'return_%d'       => '<?php return %d;',
            'comment_%d'      => '<?php // line %d',
            'blank_%d'        => "<?php\n\n\$x=%d;",
        ];

        for ($i = 0; $i < 20; $i++) {
            foreach ($templates as $name => $template) {
                $key = sprintf($name, $i);
                $cases[$key] = [
                    'code'     => sprintf($template, $i),
                    'expected' => [],
                ];
            }
        }

        return $cases;
    }
}
