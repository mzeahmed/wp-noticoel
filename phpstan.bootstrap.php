<?php

declare(strict_types=1);

/**
 * Bootstrap file for PHPStan, to tell it about the include order and avoid false positives
 * such as "Undefined constant 'ABSPATH'" and similar errors.
 */

if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__, 4));
}

// Define WordPress environment constants for PHPStan analysis
if (!defined('WP_ENV')) {
    define('WP_ENV', 'development');
}

if (!defined('ABSPATH')) {
    define('ABSPATH', PROJECT_ROOT . '/web/wp/');
}
