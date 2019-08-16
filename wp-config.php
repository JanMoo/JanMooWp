<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'janmoowp' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'ioKH~z&sm~buxy*o2@WhSkYLUkxDthDs}*;3=S%di/k3l}-d5 0Tfbp$=e{%erwF' );
define( 'SECURE_AUTH_KEY',  '[nSIM}Om{f7],:{u>wN8g[>0a~F`4AV_lCS`bPwg%}h)s0gBTUxR}mh.?zH~M$^g' );
define( 'LOGGED_IN_KEY',    '6U(*|=:E|D*UgZdZ9_^D1,OkxO@0PXsK|eL|!MS-d-ylK@lNCuj@}N@&vbU<)_7k' );
define( 'NONCE_KEY',        'Fe%.T7#?|q9JllIh6q]/Xwi(+*]YsQKlKysru_ymuW,T#aX]Fn[dDZ<Z|fOn 4Sg' );
define( 'AUTH_SALT',        '+|Wv=2)Ly@L<_F%+H4VLZK:U/;5I}aJj8NKD*au05>dSA:9vcJ(aV3mc{:;yDYQ:' );
define( 'SECURE_AUTH_SALT', '+kw=Z^GsxDM%)w G*#bG+z=]*GM]`W[/$sg%%f35MgrMCwEBed^+Ka1nM3q/,Lgk' );
define( 'LOGGED_IN_SALT',   'qoO*7<*2fEq=ct /OCx*OJZpH^K1}A,<J{XQ)))c2PH(3tR:=*G,Hk3HYJnD)G|A' );
define( 'NONCE_SALT',       ']Nm I0K4]=WD5Xwt{bSI/RQf.-H}Oh3A_Y@@fvdal.H4oWg&De*m)f#7KO2Wu#;i' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
