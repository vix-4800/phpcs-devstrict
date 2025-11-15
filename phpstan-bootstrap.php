<?php

declare(strict_types=1);

// Bootstrap file for PHPStan to load PHP_CodeSniffer constants and classes

require_once __DIR__ . '/vendor/squizlabs/php_codesniffer/autoload.php';

if (!defined('T_NONE')) {
    new \PHP_CodeSniffer\Util\Tokens();
}
