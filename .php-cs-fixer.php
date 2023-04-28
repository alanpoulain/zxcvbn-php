<?php

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit100Migration:risky' => true,
        'declare_strict_types' => true,
        'final_class' => true,
        'fopen_flags' => true,
        'method_chaining_indentation' => false,
        'nullable_type_declaration_for_default_null_value' => true,
        'ordered_types' => ['null_adjustment' => 'always_last'],
        'php_unit_internal_class' => false,
        'php_unit_strict' => false,
        'php_unit_test_class_requires_covers' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude(['vendor', 'vendor/bin'])
    )
;
