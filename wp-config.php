<?php
/**
 * Lesarge Workforce Hub — WordPress Multisite Configuration
 * Subdomain-based network: lesarge.ch, app.lesarge.ch, admin.lesarge.ch
 * 
 * SECURITY: All sensitive credentials must be set via environment variables.
 * Never hardcode credentials in this file.
 */

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env', true);
    foreach ($env as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                putenv("$key=$v");
            }
        } else {
            putenv("$key=$value");
        }
    }
}

// Database Configuration
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
define('DB_COLLATE', '');

$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
$isLocal = in_array($host, ['localhost', '127.0.0.1', 'localhost:8080', '127.0.0.1:8080'], true)
    || str_starts_with($host, 'localhost');

// Use environment variables with fallbacks for local development
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'lesarge_workforce_hub');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');

$table_prefix = 'wp_';

/* Authentication Unique Keys and Salts */
define('AUTH_KEY',         getenv('AUTH_KEY') ?: 'put_your_unique_phrase_here');
define('SECURE_AUTH_KEY',  getenv('SECURE_AUTH_KEY') ?: 'put_your_unique_phrase_here');
define('LOGGED_IN_KEY',    getenv('LOGGED_IN_KEY') ?: 'put_your_unique_phrase_here');
define('NONCE_KEY',        getenv('NONCE_KEY') ?: 'put_your_unique_phrase_here');
define('AUTH_SALT',        getenv('AUTH_SALT') ?: 'put_your_unique_phrase_here');
define('SECURE_AUTH_SALT', getenv('SECURE_AUTH_SALT') ?: 'put_your_unique_phrase_here');
define('LOGGED_IN_SALT',   getenv('LOGGED_IN_SALT') ?: 'put_your_unique_phrase_here');
define('NONCE_SALT',       getenv('NONCE_SALT') ?: 'put_your_unique_phrase_here');

/* Dynamic WP_HOME and WP_SITEURL */
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$wp_host = getenv('WP_HOME') ?: ($isLocal ? 'localhost:8080' : 'lesarge.ch');
$wp_url = $scheme . '://' . $wp_host;

define('WP_HOME', $wp_url);
define('WP_SITEURL', $wp_url);

/* Security Settings */
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', false);
define('FORCE_SSL_ADMIN', !$isLocal);
define('FORCE_SSL_LOGIN', !$isLocal);

/* Performance & Caching */
define('WP_CACHE', getenv('WP_CACHE') === 'true');
define('WP_MEMORY_LIMIT', '128M');
define('WP_MAX_MEMORY_LIMIT', '256M');

/* Debug Settings */
$wp_debug = getenv('WP_DEBUG') === 'true';
define('WP_DEBUG', $wp_debug);
define('WP_DEBUG_LOG', $wp_debug);
define('WP_DEBUG_DISPLAY', false);

/* WordPress Multisite */
define('WP_ALLOW_MULTISITE', getenv('WP_ALLOW_MULTISITE') === 'true');

if (getenv('MULTISITE') === 'true') {
    define('MULTISITE', true);
    define('SUBDOMAIN_INSTALL', true);
    define('DOMAIN_CURRENT_SITE', 'lesarge.ch');
    define('PATH_CURRENT_SITE', '/');
    define('SITE_ID_CURRENT_SITE', 1);
    define('BLOG_ID_CURRENT_SITE', 1);
}

/* Absolute path */
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
