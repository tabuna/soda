<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Config;

final class StructuralConfig extends RuleSectionConfig
{
    public function maxMethodLength(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_method_length');

        $this->entries['max_method_length'] = $value;

        return $this;
    }

    public function maxClassLength(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_class_length');

        $this->entries['max_class_length'] = $value;

        return $this;
    }

    public function maxArguments(int $value): self
    {
        ConfigAssert::nonNegativeInt($value, 'max_arguments');

        $this->entries['max_arguments'] = $value;

        return $this;
    }

    public function maxMethodsPerClass(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_methods_per_class');

        $this->entries['max_methods_per_class'] = $value;

        return $this;
    }

    public function maxFileLoc(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_file_loc');

        $this->entries['max_file_loc'] = $value;

        return $this;
    }

    public function maxPropertiesPerClass(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_properties_per_class');

        $this->entries['max_properties_per_class'] = $value;

        return $this;
    }

    public function maxPublicMethods(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_public_methods');

        $this->entries['max_public_methods'] = $value;

        return $this;
    }

    public function maxTodoFixmeComments(int $value): self
    {
        ConfigAssert::nonNegativeInt($value, 'max_todo_fixme_comments');

        $this->entries['max_todo_fixme_comments'] = $value;

        return $this;
    }

    public function maxCommentedOutCodeLines(int $value): self
    {
        ConfigAssert::nonNegativeInt($value, 'max_commented_out_code_lines');

        $this->entries['max_commented_out_code_lines'] = $value;

        return $this;
    }

    public function maxEmptyCatchBlocks(int $value): self
    {
        ConfigAssert::nonNegativeInt($value, 'max_empty_catch_blocks');

        $this->entries['max_empty_catch_blocks'] = $value;

        return $this;
    }

    public function maxAskThenTellPatterns(int $value): self
    {
        ConfigAssert::nonNegativeInt($value, 'max_ask_then_tell_patterns');

        $this->entries['max_ask_then_tell_patterns'] = $value;

        return $this;
    }

    public function maxDependencies(int $value): self
    {
        ConfigAssert::nonNegativeInt($value, 'max_dependencies');

        $this->entries['max_dependencies'] = $value;

        return $this;
    }

    public function maxEfferentCoupling(int $value): self
    {
        ConfigAssert::nonNegativeInt($value, 'max_efferent_coupling');

        $this->entries['max_efferent_coupling'] = $value;

        return $this;
    }

    public function maxClassesPerFile(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_classes_per_file');

        $this->entries['max_classes_per_file'] = $value;

        return $this;
    }

    public function maxNamespaceDepth(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_namespace_depth');

        $this->entries['max_namespace_depth'] = $value;

        return $this;
    }

    public function maxClassesPerNamespace(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_classes_per_namespace');

        $this->entries['max_classes_per_namespace'] = $value;

        return $this;
    }

    public function maxLayerDominancePercentage(int $threshold, int $minFiles): self
    {
        ConfigAssert::positiveInt($threshold, 'max_layer_dominance_percentage.threshold');
        ConfigAssert::positiveInt($minFiles, 'max_layer_dominance_percentage.min_files');

        $this->entries['max_layer_dominance_percentage'] = [
            'threshold'   => $threshold,
            'min_files'   => $minFiles,
        ];

        return $this;
    }

    public function maxTraitsPerClass(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_traits_per_class');

        $this->entries['max_traits_per_class'] = $value;

        return $this;
    }

    public function maxInterfacesPerClass(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_interfaces_per_class');

        $this->entries['max_interfaces_per_class'] = $value;

        return $this;
    }

    public function maxClassesPerProject(int $value): self
    {
        ConfigAssert::positiveInt($value, 'max_classes_per_project');

        $this->entries['max_classes_per_project'] = $value;

        return $this;
    }
}
