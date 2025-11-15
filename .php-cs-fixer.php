<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PHP74Migration' => true,
        '@PhpCsFixer' => true,

        // Array notation
        'array_syntax' => ['syntax' => 'short'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'trim_array_spaces' => true,
        'whitespace_after_comma_in_array' => true,

        // Basic
        'braces' => [
            'allow_single_line_closure' => true,
            'position_after_functions_and_oop_constructs' => 'next',
        ],
        'encoding' => true,
        'full_opening_tag' => true,

        // Casing
        'constant_case' => ['case' => 'lower'],
        'lowercase_keywords' => true,
        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,
        'native_function_type_declaration_casing' => true,

        // Cast notation
        'cast_spaces' => ['space' => 'single'],
        'lowercase_cast' => true,
        'no_short_bool_cast' => true,
        'short_scalar_cast' => true,

        // Class notation
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'class_definition' => [
            'multi_line_extends_each_single_line' => true,
            'single_item_single_line' => true,
            'single_line' => true,
        ],
        'no_blank_lines_after_class_opening' => true,
        'ordered_class_elements' => [
            'order' => [
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
                'method_private',
            ],
        ],
        'protected_to_private' => true,
        'self_accessor' => true,
        'self_static_accessor' => true,
        'single_class_element_per_statement' => true,
        'visibility_required' => ['elements' => ['property', 'method', 'const']],

        // Comment
        'multiline_comment_opening_closing' => true,
        'no_empty_comment' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],

        // Control structure
        'control_structure_continuation_position' => ['position' => 'same_line'],
        'no_alternative_syntax' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'simplified_if_return' => true,
        'switch_continue_to_break' => true,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],

        // Function notation
        'function_declaration' => ['closure_function_spacing' => 'one'],
        'function_typehint_space' => true,
        'lambda_not_used_import' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'no_spaces_after_function_name' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'single_line_throw' => false,

        // Import
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'no_leading_import_slash' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,

        // Language construct
        'declare_equal_normalize' => ['space' => 'none'],
        'declare_strict_types' => true,

        // Namespace notation
        'blank_line_after_namespace' => true,
        'no_leading_namespace_whitespace' => true,

        // Operator
        'binary_operator_spaces' => ['default' => 'single_space'],
        'concat_space' => ['spacing' => 'one'],
        'not_operator_with_successor_space' => false,
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => ['only_booleans' => true],
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_null_coalescing' => true,
        'unary_operator_spaces' => true,

        // PHP tag
        'blank_line_after_opening_tag' => true,
        'linebreak_after_opening_tag' => true,
        'no_closing_tag' => true,

        // PHPDoc
        'align_multiline_comment' => ['comment_type' => 'phpdocs_only'],
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'allow_unused_params' => false,
        ],
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_package' => true,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => false,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,

        // Return notation
        'no_useless_return' => true,
        'return_assignment' => true,
        'simplified_null_return' => false,

        // Semicolon
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'no_empty_statement' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'space_after_semicolon' => ['remove_in_empty_for_expressions' => true],

        // Strict
        'strict_comparison' => true,
        'strict_param' => true,

        // String notation
        'explicit_string_variable' => true,
        'simple_to_complex_string_variable' => true,
        'single_quote' => true,

        // Whitespace
        'array_indentation' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'try', 'throw', 'declare'],
        ],
        'compact_nullable_typehint' => true,
        'heredoc_indentation' => ['indentation' => 'start_plus_one'],
        'indentation_type' => true,
        'line_ending' => true,
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'throw',
                'use',
                'use_trait',
            ],
        ],
        'no_spaces_around_offset' => true,
        'no_spaces_inside_parenthesis' => true,
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
    ])
    ->setFinder($finder);
