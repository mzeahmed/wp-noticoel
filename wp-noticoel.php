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

require_once __DIR__ . '/vendor/autoload.php';

function wpNoticoel(): void
{
    add_action('plugins_loaded', function () {
        (new \WpNoticoel\EventListener())->register();
    });
}

wpNoticoel();
