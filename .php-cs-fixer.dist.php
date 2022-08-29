<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setFinder(
        Finder::create()
            ->in(__DIR__ . '/src')
    )
    ->setRules([
        '@PHP80Migration:risky' => true,
        '@PHP81Migration' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false, 'always_move_variable' => true],
        'php_unit_test_case_static_method_calls' => ['call_type' => 'this'],
        'php_unit_method_casing' => false,
        'php_unit_test_annotation' => ['style' => 'annotation'],
    ])
    ->setRiskyAllowed(true)
    ->setCacheFile('/tmp/php-cs-fixer.cache');
