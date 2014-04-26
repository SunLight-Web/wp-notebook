<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'black_one');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
<<<<<<< HEAD
define('DB_PASSWORD', '');
=======
define('DB_PASSWORD', 'root');
>>>>>>> FETCH_HEAD

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'q=to!:winsd>;koLf`KXwE);2In:Ep%^]Ew^xaR-[7.h{0ih{(QX -yx;5!U~I7$');
define('SECURE_AUTH_KEY',  'C2eeK+^7B%+<y^^rN6pYtJa|ecoyrB&h g3<wyX$ZI8Kz+GyZ0zg=VrDe]pcgT92');
define('LOGGED_IN_KEY',    '*2a*K+?C6-hyuZt%7f[)jOC#BR{l~e$7~lUn7psgwZp6T]baciF7<_NCDoq1HV2]');
define('NONCE_KEY',        'pX&Ym{nG*NNUP/HXF--,q@2nt/>Bb::M!TTQ<e|D*JI]-gkq^+a`SL ASD@JU=rH');
define('AUTH_SALT',        '<5%Cw-B2 3_CmF8B#E{quJroT>xA&(T+b|qhOY7z&lpZ-voX|LZ`K<7JdaAu8$bu');
define('SECURE_AUTH_SALT', '73]!g;dgceB{_bt}2LF+ms&Ax0xB82$jd1NFHt<zdX]3-.=jk(mmz/dkVbJVRkXY');
define('LOGGED_IN_SALT',   'WC+wUTxx,rl6#5;zS*e)mG<>&w.jSpL4KOQk.][9XD[|EIFyKAbH8=%QbR,b,Tbz');
define('NONCE_SALT',       'g.+fc5Iox,=+<uR,U#z5*ZPp*bDl2T B*-%<&ZPngRGkFGH:s@DH>YH]7:`kEK@{');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
