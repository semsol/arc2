<?php

return PhpCsFixer\Config::create()
    ->setRules(
        [
            '@Symfony' => true,
            '@Symfony:risky' => true,
            'align_multiline_comment' => true,
            'array_syntax' => ['syntax' => 'short'],
            'array_indentation' => true,
        ]
    )
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
        ->files()
        ->in(__DIR__ . '/parsers')
        ->in(__DIR__ . '/serializers')
        ->in(__DIR__ . '/src')
        ->in(__DIR__ . '/store')
        ->name('*.php')
    );
