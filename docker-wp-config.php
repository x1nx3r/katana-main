<?php
/**
 * Docker-optimized WordPress configuration
 * 
 * This wp-config.php is specifically designed for Docker containers
 * and will work with both local development and Coolify deployment
 */

// ** Database settings - Using Docker environment variables ** //
define( 'DB_NAME', getenv('WORDPRESS_DB_NAME') ?: 'ptka_tana' );
define( 'DB_USER', getenv('WORDPRESS_DB_USER') ?: 'ptka_tana' );
define( 'DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD') ?: 'ptka_tana' );
define( 'DB_HOST', getenv('WORDPRESS_DB_HOST') ?: 'db' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

/**
 * WordPress Database Table prefix.
 */
$table_prefix = getenv('WORDPRESS_TABLE_PREFIX') ?: 'wphc_';

/**
 * Authentication unique keys and salts.
 * These are the same as in your original wp-config.php for data consistency
 */
define( 'AUTH_KEY',         'bls77nm3vrxbpxuh4dl7gk4efc962idk4fqufp9tyvrnljr5kdtmfilexywxxlsm' );
define( 'SECURE_AUTH_KEY',  'cpjtuu39vaep8uldhc4qvguneb5bcso9k7kz2ugehvhrwyjhapah1doyhxgqnqrq' );
define( 'LOGGED_IN_KEY',    'y13yobblsoflz6k9ue8drjx1n3bkqahcnww0sifwfdparefviuql5ar5jpeqrwq5' );
define( 'NONCE_KEY',        's5ppyqkvsglsha4w0nlng91xtlbbb7nxi87m0hopic6hogewwtyqbraixrjngcce' );
define( 'AUTH_SALT',        'jxihwfrcfbdjehri55gzhncfrw8jdbzupxyhgodqh9mb3zjammisur5fqnl5grsy' );
define( 'SECURE_AUTH_SALT', 'a7ttdlqvcg8ztl9kg4xg1sddjc8oruiu4tsiwtbjmtohkdo38j8nqeerktdhhiha' );
define( 'LOGGED_IN_SALT',   'xhgeof83evrugfwal4onb2djpiprgbvg1enahgietjdsvtwezrxtgoxcwsohrv8m' );
define( 'NONCE_SALT',       'vej6qkrgjmbsnvrkpov2bqhi5yo9f0iwyuvhxlpeyidxq8t8pe83avptdb17rjg4' );

/**
 * WordPress debugging mode.
 * Enable debugging in development, disable in production
 */
define( 'WP_DEBUG', getenv('WORDPRESS_DEBUG') === 'true' );
define( 'WP_DEBUG_LOG', getenv('WORDPRESS_DEBUG') === 'true' );
define( 'WP_DEBUG_DISPLAY', false );

/**
 * WordPress Cache and Performance Settings
 */
define( 'WP_CACHE', true );
define( 'FORCE_SSL_ADMIN', getenv('FORCE_SSL_ADMIN') === 'true' );

/**
 * Automatic Updates
 */
define( 'WP_AUTO_UPDATE_CORE', false );

/**
 * File permissions
 */
define( 'FS_METHOD', 'direct' );

/**
 * WordPress URLs - Let WordPress handle these automatically in Docker
 */
if ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
    $_SERVER['HTTPS'] = 'on';
}

// Handle reverse proxy headers (for Coolify/Traefik)
if ( isset($_SERVER['HTTP_X_FORWARDED_HOST']) ) {
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
}

/**
 * Custom WordPress URL settings for Docker
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    // WP-CLI specific settings
} elseif ( isset($_SERVER['HTTP_HOST']) ) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    define( 'WP_HOME', $protocol . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_SITEURL', $protocol . '://' . $_SERVER['HTTP_HOST'] );
}

/* Add any custom values between this line and the "stop editing" line. */

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';