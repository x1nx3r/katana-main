<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ptka_tana' );

/** Database username */
define( 'DB_USER', 'ptka_tana' );

/** Database password */
define( 'DB_PASSWORD', 'ptka_tana' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'bls77nm3vrxbpxuh4dl7gk4efc962idk4fqufp9tyvrnljr5kdtmfilexywxxlsm' );
define( 'SECURE_AUTH_KEY',  'cpjtuu39vaep8uldhc4qvguneb5bcso9k7kz2ugehvhrwyjhapah1doyhxgqnqrq' );
define( 'LOGGED_IN_KEY',    'y13yobblsoflz6k9ue8drjx1n3bkqahcnww0sifwfdparefviuql5ar5jpeqrwq5' );
define( 'NONCE_KEY',        's5ppyqkvsglsha4w0nlng91xtlbbb7nxi87m0hopic6hogewwtyqbraixrjngcce' );
define( 'AUTH_SALT',        'jxihwfrcfbdjehri55gzhncfrw8jdbzupxyhgodqh9mb3zjammisur5fqnl5grsy' );
define( 'SECURE_AUTH_SALT', 'a7ttdlqvcg8ztl9kg4xg1sddjc8oruiu4tsiwtbjmtohkdo38j8nqeerktdhhiha' );
define( 'LOGGED_IN_SALT',   'xhgeof83evrugfwal4onb2djpiprgbvg1enahgietjdsvtwezrxtgoxcwsohrv8m' );
define( 'NONCE_SALT',       'vej6qkrgjmbsnvrkpov2bqhi5yo9f0iwyuvhxlpeyidxq8t8pe83avptdb17rjg4' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wphc_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
