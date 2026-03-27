<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use function is_array;
use function is_numeric;

use LogicException;

use function sprintf;
use function var_export;

/**
 * @internal
 */
final class StructuralRuleStatementPhpEmitter
{
    /** @var array<string, string> */
    private const array INT_METHODS = [
        'max_method_length'            => 'maxMethodLength',
        'max_class_length'             => 'maxClassLength',
        'max_arguments'                => 'maxArguments',
        'max_methods_per_class'        => 'maxMethodsPerClass',
        'max_file_loc'                 => 'maxFileLoc',
        'max_properties_per_class'     => 'maxPropertiesPerClass',
        'max_public_methods'           => 'maxPublicMethods',
        'max_todo_fixme_comments'      => 'maxTodoFixmeComments',
        'max_commented_out_code_lines' => 'maxCommentedOutCodeLines',
        'max_empty_catch_blocks'       => 'maxEmptyCatchBlocks',
        'max_ask_then_tell_patterns'   => 'maxAskThenTellPatterns',
        'max_dependencies'             => 'maxDependencies',
        'max_efferent_coupling'        => 'maxEfferentCoupling',
        'max_classes_per_file'         => 'maxClassesPerFile',
        'max_namespace_depth'          => 'maxNamespaceDepth',
        'max_classes_per_namespace'    => 'maxClassesPerNamespace',
        'max_traits_per_class'         => 'maxTraitsPerClass',
        'max_interfaces_per_class'     => 'maxInterfacesPerClass',
        'max_classes_per_project'      => 'maxClassesPerProject',
    ];

    public static function emit(string $ruleId, mixed $value): string
    {
        if ($ruleId === 'max_layer_dominance_percentage') {
            return self::emitLayerDominance($value);
        }

        $method = self::INT_METHODS[$ruleId] ?? null;

        if ($method === null) {
            throw new LogicException('Unknown structural rule: '.$ruleId);
        }

        if (! is_numeric($value)) {
            throw new LogicException(sprintf('Structural rule "%s" expects a number.', $ruleId));
        }

        return sprintf('$config->structural()->%s(%s);', $method, var_export((int) $value, true));
    }

    private static function emitLayerDominance(mixed $value): string
    {
        if (! is_array($value)) {
            throw new LogicException('max_layer_dominance_percentage expects array.');
        }

        $t = $value['threshold'] ?? null;
        $m = $value['min_files'] ?? null;

        if (! is_numeric($t) || ! is_numeric($m)) {
            throw new LogicException('max_layer_dominance_percentage requires threshold and min_files.');
        }

        return sprintf(
            '$config->structural()->maxLayerDominancePercentage(%d, %d);',
            (int) $t,
            (int) $m,
        );
    }
}
