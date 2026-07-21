<?php

declare(strict_types=1);

/**
 * Fichier bootstrap pour PHPStan, pour preciser à PHPStan l'ordre d'inclusion des fichiers et éviter l'affichage des
 * erreurs qui n'en sont pas, comme les erreurs de type "Undefined constant 'ABSPATH'", ou autre.
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
