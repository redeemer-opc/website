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
define('DB_NAME', 'ropc16');

/** MySQL database username */
define('DB_USER', 'redeemeropc_wp');

/** MySQL database password */
define('DB_PASSWORD', 'b4@d2&b2$f4%');

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
define('AUTH_KEY',         '6%:q/.EGjc~HEQ(rD<%wn!Yw2yN~.|9@SqcuK[ysC6UE)@iFEgk>lycuA9l@ 1jW');
define('SECURE_AUTH_KEY',  ';gM[t8@V*[$16j}cM%S&%6ji;h/)?z(e+G$.>ZFJF+tB+K7!h`j<!g%QkJiM$%FE');
define('LOGGED_IN_KEY',    'c7c)!RM]68yF&Vpe(0K4S$|#R1Cz~fgn1JUM*6;zOMYU8&{rPb!Wa.-<*5KedyW/');
define('NONCE_KEY',        'qC#phAV|S.L ;e7bi!6+KVG)mB_:^gH1X|HyOstU ;3>KW/ wuLZaD4/BkODG-)1');
define('AUTH_SALT',        '<57emdA/7RD!(T+k-xSJ4f%Ycv//v?8Tel/F|ZLy^p@q:*_z=q[7P,%ur~jin+fx');
define('SECURE_AUTH_SALT', 'j>!pijL4*5y/7Jt0cy*pV*LF5dx+:f$];b;8E#b,CGKY<rP/ g$*EaAf|K>Wvqz|');
define('LOGGED_IN_SALT',   '^rn:,_iXr+ TM{U|+oN, q:-|&|9&=iW4C`d3(x_g/rQ^HQ<l?BipR5DwI?ZRP`h');
define('NONCE_SALT',       'v+>?]f-?3v:5%i*,NMYO?tj%jcs<v^,{DtDlUm&Cj;u5U<u5]e$9G^;i<cf mo>2');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
