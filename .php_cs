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
        ->in(__DIR__.'/extractors')
        ->in(__DIR__.'/parsers')
        ->in(__DIR__.'/serializers')
        ->in(__DIR__.'/sparqlscript')
        ->in(__DIR__.'/src')
        ->in(__DIR__.'/store')
        ->in(__DIR__.'/tests')
        ->name('*.php')
        ->append([
            __FILE__,
            __DIR__.'/ARC2.php',
            __DIR__.'/ARC2_Class.php',
            __DIR__.'/ARC2_getFormat.php',
            __DIR__.'/ARC2_getPreferredFormat.php',
            __DIR__.'/ARC2_Graph.php',
            __DIR__.'/ARC2_Reader.php',
            __DIR__.'/ARC2_Ressource.php',
        ])
    );
