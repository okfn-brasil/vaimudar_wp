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
define('DB_NAME', 'proprietarios');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

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
define('AUTH_KEY',         ',s:1e9pq}SfI1M100USd[E-yaC;~6$UgHeFz/|{c4`MpR*VG;.sMMVtQ6p4)|i:0');
define('SECURE_AUTH_KEY',  '>|N|Cux7t3#nm;[#G3rG|Z2Xbvm.y) *+wWH#wzQGI|gN87+]F>{}e&-T-]?0!{u');
define('LOGGED_IN_KEY',    'Fe;x&dr-_&l(a:rNBCS5l]f}D)z--,wy/[>;,UHLDwLy|Hm_-60]waS%@tv*Mke6');
define('NONCE_KEY',        'bZ_b2i5&NQ0Z_T)g|R&K2^x8L.xjp89{z?<6M3&X90o0/`qA3QS=*L6;GAqarl,O');
define('AUTH_SALT',        '`{WgqT:.}%b;z,1-V3vmX`^S`VLv?{]Kj|^|?U_z49`{6=k`Dz2=P+w3-KcS5So.');
define('SECURE_AUTH_SALT', '8M&GSre#RslSMm~DC{&vB!R+Q{Iq;|a)A1!X]WC8@u@> i|y##3Hp5SI%PYw@O]J');
define('LOGGED_IN_SALT',   ' ) At9+-tM3|Bsg$hybIWxw;?6[Bj|fbqkn!b+^Aas7V,.a@Gn1{j$< e+T>@z-O');
define('NONCE_SALT',       'HId2fw6+m$?>rJA-?)4m@-|wEq1ElMW|gHI&,u-H^cLHcDgc >!F?FMBfYl<gwjT');

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

#define('WP_ALLOW_REPAIR', true);
