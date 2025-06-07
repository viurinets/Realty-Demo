<?php
/**
 * The base configuration for WordPress using Docker and PostgreSQL
 */

// Helper function to read environment variables or fallback values
if (!function_exists('getenv_docker')) {
define('WP_CACHE', true);
define( 'WPCACHEHOME', '/var/www/html/wp-content/plugins/wp-super-cache/' );
    function getenv_docker($env, $default) {
        if ($fileEnv = getenv($env . '_FILE')) {
            return rtrim(file_get_contents($fileEnv), "\r\n");
        } elseif (($val = getenv($env)) !== false) {
            return $val;
        } else {
            return $default;
        }
    }
}

// ** Database settings ** //
define('DB_NAME', getenv_docker('WORDPRESS_DB_NAME', 'wordpress'));
define('DB_USER', getenv_docker('WORDPRESS_DB_USER', 'user'));
define('DB_PASSWORD', getenv_docker('WORDPRESS_DB_PASSWORD', 'password'));
define('DB_HOST', getenv_docker('WORDPRESS_DB_HOST', 'postgres'));
define('DB_CHARSET', getenv_docker('WORDPRESS_DB_CHARSET', 'utf8'));
define('DB_COLLATE', getenv_docker('WORDPRESS_DB_COLLATE', ''));
define('FS_METHOD', 'direct');


// ** Authentication Unique Keys and Salts ** //
define('AUTH_KEY', getenv_docker('WORDPRESS_AUTH_KEY', 'put your unique phrase here'));
define('SECURE_AUTH_KEY', getenv_docker('WORDPRESS_SECURE_AUTH_KEY', 'put your unique phrase here'));
define('LOGGED_IN_KEY', getenv_docker('WORDPRESS_LOGGED_IN_KEY', 'put your unique phrase here'));
define('NONCE_KEY', getenv_docker('WORDPRESS_NONCE_KEY', 'put your unique phrase here'));
define('AUTH_SALT', getenv_docker('WORDPRESS_AUTH_SALT', 'put your unique phrase here'));
define('SECURE_AUTH_SALT', getenv_docker('WORDPRESS_SECURE_AUTH_SALT', 'put your unique phrase here'));
define('LOGGED_IN_SALT', getenv_docker('WORDPRESS_LOGGED_IN_SALT', 'put your unique phrase here'));
define('NONCE_SALT', getenv_docker('WORDPRESS_NONCE_SALT', 'put your unique phrase here'));

// ** Table prefix ** //
$table_prefix = getenv_docker('WORDPRESS_TABLE_PREFIX', 'wp_');

// ** Debugging settings ** //
define('WP_DEBUG', filter_var(getenv_docker('WORDPRESS_DEBUG', false), FILTER_VALIDATE_BOOLEAN));
define('WP_DEBUG_LOG', filter_var(getenv_docker('WORDPRESS_DEBUG_LOG', false), FILTER_VALIDATE_BOOLEAN));
define('WP_DEBUG_DISPLAY', filter_var(getenv_docker('WORDPRESS_DEBUG_DISPLAY', false), FILTER_VALIDATE_BOOLEAN));

// ** Handle HTTPS behind reverse proxies ** //
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
    $_SERVER['HTTPS'] = 'on';
}

// ** Optional extra configuration ** //
if ($configExtra = getenv_docker('WORDPRESS_CONFIG_EXTRA', '')) {
    eval($configExtra);
}

// ** Absolute path to the WordPress directory ** //
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// ** Set up WordPress vars and include files ** //
require_once ABSPATH . 'wp-settings.php';
