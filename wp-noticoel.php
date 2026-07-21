<?php

declare(strict_types=1);

/**
 * Plugin Name: WP Noticoel
 * Description: Forwards WordPress application events to a Noticoel hub over HTTP.
 * Version: 0.1.0
 * Author: Ahmed Mze
 * License: MIT
 * Text Domain: wp-noticoel
 */

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

define('NOTICOEL_PLUGIN_FILE', __FILE__);

if (!defined('NOTICOEL_PLUGIN_PATH')) {
    define('NOTICOEL_PLUGIN_PATH', dirname(NOTICOEL_PLUGIN_FILE));
}

if (!defined('NOTICOEL_PLUGIN_URL')) {
    define('NOTICOEL_PLUGIN_URL', dirname(NOTICOEL_PLUGIN_FILE));
}

if (!defined('NOTICOEL_PLUGIN_BASENAME')) {
    define('NOTICOEL_PLUGIN_BASENAME', dirname(NOTICOEL_PLUGIN_FILE));
}

$autoload = rtrim(NOTICOEL_PLUGIN_PATH) . '/vendor/autoload.php';

if (file_exists($autoload)) {
    require_once $autoload;
} else {
    wp_die(__('Composer autoloader not found.', 'wp-noticoel'));
}

function wpNoticoel(): void
{
    add_action('plugins_loaded', function () {
        (new \WpNoticoel\EventListener())->register();
    });
}

wpNoticoel();
