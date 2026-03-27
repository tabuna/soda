<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

use InvalidArgumentException;

use function is_array;
use function is_numeric;

/**
 * @internal
 */
final class NestedRulesStructuralApplier
{
    /**
     * @var array<string, callable(SodaConfig, int): void>|null
     */
    private static ?array $intAppliers = null;

    public static function apply(SodaConfig $config, string $ruleId, mixed $value): void
    {
        if ($ruleId === 'max_layer_dominance_percentage') {
            self::applyLayerDominance($config, $value);

            return;
        }

        if (! is_numeric($value)) {
            throw new InvalidArgumentException(sprintf('Structural rule "%s" expects a number.', $ruleId));
        }

        $n = (int) $value;
        $map = self::intAppliers();

        if (isset($map[$ruleId])) {
            $map[$ruleId]($config, $n);

            return;
        }

        throw new InvalidArgumentException('Unknown structural rule: '.$ruleId);
    }

    private static function applyLayerDominance(SodaConfig $config, mixed $value): void
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException('max_layer_dominance_percentage must be an array with threshold and min_files.');
        }

        $threshold = $value['threshold'] ?? null;
        $minFiles = $value['min_files'] ?? null;

        if (! is_numeric($threshold) || ! is_numeric($minFiles)) {
            throw new InvalidArgumentException('max_layer_dominance_percentage requires numeric threshold and min_files.');
        }

        $config->structural()->maxLayerDominancePercentage((int) $threshold, (int) $minFiles);
    }

    /**
     * @return array<string, callable(SodaConfig, int): void>
     */
    private static function intAppliers(): array
    {
        return self::$intAppliers ??= [
            'max_method_length'            => static fn (SodaConfig $c, int $n) => $c->structural()->maxMethodLength($n),
            'max_class_length'             => static fn (SodaConfig $c, int $n) => $c->structural()->maxClassLength($n),
            'max_arguments'                => static fn (SodaConfig $c, int $n) => $c->structural()->maxArguments($n),
            'max_methods_per_class'        => static fn (SodaConfig $c, int $n) => $c->structural()->maxMethodsPerClass($n),
            'max_file_loc'                 => static fn (SodaConfig $c, int $n) => $c->structural()->maxFileLoc($n),
            'max_properties_per_class'     => static fn (SodaConfig $c, int $n) => $c->structural()->maxPropertiesPerClass($n),
            'max_public_methods'           => static fn (SodaConfig $c, int $n) => $c->structural()->maxPublicMethods($n),
            'max_todo_fixme_comments'      => static fn (SodaConfig $c, int $n) => $c->structural()->maxTodoFixmeComments($n),
            'max_commented_out_code_lines' => static fn (SodaConfig $c, int $n) => $c->structural()->maxCommentedOutCodeLines($n),
            'max_empty_catch_blocks'       => static fn (SodaConfig $c, int $n) => $c->structural()->maxEmptyCatchBlocks($n),
            'max_ask_then_tell_patterns'   => static fn (SodaConfig $c, int $n) => $c->structural()->maxAskThenTellPatterns($n),
            'max_dependencies'             => static fn (SodaConfig $c, int $n) => $c->structural()->maxDependencies($n),
            'max_efferent_coupling'        => static fn (SodaConfig $c, int $n) => $c->structural()->maxEfferentCoupling($n),
            'max_classes_per_file'         => static fn (SodaConfig $c, int $n) => $c->structural()->maxClassesPerFile($n),
            'max_namespace_depth'          => static fn (SodaConfig $c, int $n) => $c->structural()->maxNamespaceDepth($n),
            'max_classes_per_namespace'    => static fn (SodaConfig $c, int $n) => $c->structural()->maxClassesPerNamespace($n),
            'max_traits_per_class'         => static fn (SodaConfig $c, int $n) => $c->structural()->maxTraitsPerClass($n),
            'max_interfaces_per_class'     => static fn (SodaConfig $c, int $n) => $c->structural()->maxInterfacesPerClass($n),
            'max_classes_per_project'      => static fn (SodaConfig $c, int $n) => $c->structural()->maxClassesPerProject($n),
        ];
    }
}
