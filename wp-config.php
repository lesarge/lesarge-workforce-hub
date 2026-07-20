<?php
/**
 * Lesarge Workforce Hub — WordPress Multisite Configuration
 * Subdomain-based network: lesarge.ch, app.lesarge.ch, admin.lesarge.ch
 */

define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
$isLocal = in_array($host, ['localhost', '127.0.0.1', 'localhost:8080', '127.0.0.1:8080'], true)
    || str_starts_with($host, 'localhost');

define('DB_HOST', $isLocal ? '127.0.0.1' : 'liebst2.mysql.db.internal');
define('DB_NAME', $isLocal ? 'lesarge_workforce_hub' : 'liebst2_hub');
define('DB_USER', $isLocal ? 'root' : 'liebst2_hub');
define('DB_PASSWORD', $isLocal ? '' : 'Sonja@34?');

$table_prefix = 'wp_';

/* Authentication Unique Keys and Salts */
define('AUTH_KEY',         'lwh-$ecureK3y!2026#Prod-auth-xk9m2zq7p3jr4n8');
define('SECURE_AUTH_KEY',  'lwh-$ecureK3y!2026#Prod-secure-zq7p3kf2m5bn5t2');
define('LOGGED_IN_KEY',    'lwh-$ecureK3y!2026#Prod-logged-jr4n8wt6q1dv3s7');
define('NONCE_KEY',        'lwh-$ecureK3y!2026#Prod-nonce-kf2m5hy8r4bn5t2');
define('AUTH_SALT',        'lwh-$ecureK3y!2026#Prod-salt-wt6q1dv3s7xk9m2');
define('SECURE_AUTH_SALT', 'lwh-$ecureK3y!2026#Prod-sasalt-hy8r4bn5t2zq7p3');
define('LOGGED_IN_SALT',   'lwh-$ecureK3y!2026#Prod-lsalt-dv3s7xk9m2zq7p3');
define('NONCE_SALT',       'lwh-$ecureK3y!2026#Prod-nsalt-kf2m5hy8r4bn5t2');

/* Dynamic WP_HOME and WP_SITEURL */
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$wp_host = $isLocal ? 'localhost:8080' : 'lesarge.ch';
$wp_url = $scheme . '://' . $wp_host;

define('WP_HOME', $wp_url);
define('WP_SITEURL', $wp_url);

/* Security */
define('DISALLOW_FILE_EDIT', true);
define('WP_CACHE', false);

/* ── WordPress Multisite (Subdomain) ──────────────────────────── */
/* Enable after initial WP install: Dashboard > Tools > Network Setup */
define('WP_ALLOW_MULTISITE', true);

/* Absolute path */
define('ABSPATH', __DIR__ . '/');

require_once ABSPATH . 'wp-settings.php';
