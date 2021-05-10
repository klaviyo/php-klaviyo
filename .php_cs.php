<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

$config = new PhpCsFixer\Config();

return $config
    ->setRules(
        [
            '@PSR2' => true,
            'declare_strict_types' => true,
            'global_namespace_import' => [
                'import_classes' => true,
                'import_constants' => true,
                'import_functions' => true
            ],
            'list_syntax' => ['syntax' => 'short'],
            'logical_operators' => true,
            'mb_str_functions' => true,
            'method_chaining_indentation' => true,
            'new_with_braces' => true,
            'no_alternative_syntax' => true,
            'no_closing_tag' => true,
            'no_leading_import_slash' => true,
            'no_null_property_initialization' => true,
            'no_short_bool_cast' => true,
            'echo_tag_syntax' => true,
            'no_unused_imports' => true,
            'no_useless_else' => true,
            'no_useless_return' => true,
            'ternary_to_null_coalescing' => true,
            'trailing_comma_in_multiline' => true,
            'array_syntax' => ['syntax' => 'short'],
            'blank_line_after_opening_tag' => true,
            'cast_spaces' => ['space' => 'single'],
            'class_attributes_separation' => ['elements' => ['method' => 'one']],
            'compact_nullable_typehint' => true,
            'concat_space' => ['spacing' => 'one'],
            'constant_case' => ['case' => 'lower'],
            'explicit_indirect_variable' => true,
            'lowercase_cast' => true,
            'lowercase_static_reference' => true,
            'magic_constant_casing' => true,
            'multiline_whitespace_before_semicolons' => true,
            'native_function_type_declaration_casing' => true,
            'no_blank_lines_after_phpdoc' => true,
            'no_empty_statement' => true,
            'no_extra_blank_lines' => true,
            'no_leading_namespace_whitespace' => true,
            'no_multiline_whitespace_around_double_arrow' => true,
            'no_spaces_after_function_name' => true,
            'no_spaces_around_offset' => true,
            'normalize_index_brace' => true,
            'object_operator_without_whitespace' => true,
            'ordered_class_elements' => [
                "order" => [
                    'use_trait',
                    'constant_public',
                    'constant_protected',
                    'constant_private',
                    'property_public',
                    'property_protected',
                    'property_private',
                    'construct',
                    'destruct',
                    'magic',
                    'phpunit',
                    'method_public',
                    'method_protected',
                    'method_private'
                ]
            ],
            'php_unit_method_casing' => ['case' => 'snake_case'],
            'return_type_declaration' => ['space_before' => 'one'],
            'semicolon_after_instruction' => true,
            'single_blank_line_before_namespace' => true,
            'single_trait_insert_per_statement' => true,
            'standardize_increment' => true,
            'standardize_not_equals' => true,
            'ternary_operator_spaces' => true,
            'trim_array_spaces' => true,
            'whitespace_after_comma_in_array' => true,
        ]
    )
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setHideProgress(false)
    ->setUsingCache(false);