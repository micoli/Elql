<?php

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        'declare_strict_types' => true,
        'phpdoc_align' => false,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'concat_space' => ['spacing' => 'one'],
        'header_comment' => ['header' => '', 'separate' => 'both'],
        'multiline_whitespace_before_semicolons' => false,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],
        'phpdoc_order' => true,
        'array_syntax' => ['syntax' => 'short'],
        'echo_tag_syntax' => ['format' => 'long'],
        'php_unit_method_casing' => false,
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_internal_class' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'final_internal_class' => false,
        'increment_style' => ['style' => 'pre'],
        'return_type_declaration' => ['space_before' => 'none'],
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => false, 'import_functions' => false],
        'void_return' => true,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
        ],
        'class_definition' => [
            'multi_line_extends_each_single_line' => true,
        ],
        'single_line_throw' => false,
        'compact_nullable_typehint' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in([
                __DIR__ . '/src',
                __DIR__ . '/tests',
            ])
            ->name('*.php'),
    );
