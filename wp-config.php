<?php
define( 'WP_CACHE', true );

/**
 * The base configuration for WordPress
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings ** //
define( 'DB_NAME',     'u753768407_KbJ65' );
define( 'DB_USER',     'u753768407_K0EQh' );
define( 'DB_PASSWORD', 'nSSs1J2X3C' );
define( 'DB_HOST',     '127.0.0.1' );
define( 'DB_CHARSET',  'utf8' );
define( 'DB_COLLATE',  '' );

/**#@+
 * Authentication Unique Keys and Salts.
 * Change these to different unique phrases!
 */
define( 'AUTH_KEY',         '>`3tZ+?!q*Dot>.3~U;}xICPD~ 8Am-6+f++_c*YGs_b:x$B#hF$3n_vnzp^f9FL' );
define( 'SECURE_AUTH_KEY',  'R_wmiH.O/O+(aVZ-!Us(_Ukod?.iOwhMp[&[gC1AU,kA>KNuMmG>l]~YCen:nz-&' );
define( 'LOGGED_IN_KEY',    'Owf0JzBI^Wv?*cBZUuKcsIWnnSWeBLCx;;va@B!^2>nL(:,hYh%cL8k,)u8BU^}$' );
define( 'NONCE_KEY',        'u*>C>OuU[vSn41HlG^WS$8+>&1,%%j@`cu*(04jX{AcckP^Z&=Bi)vn8OM^6CjjV' );
define( 'AUTH_SALT',        'h3`PmT&WyhL?O>N!>{T2tg*],gm*c(gw;@2;_CY<Y[]F}nIFLpFnuu6#smQ6Rt{e' );
define( 'SECURE_AUTH_SALT', 'xUGlm7UV _s.0G<7j.+VoNGbu*QWQm%6Ur&m4G=oLyJ87UZ-Lgjy]_.<(V{[jod(' );
define( 'LOGGED_IN_SALT',   'k{7U)n4K];0!: j_z5xfL]Lv)1pPaV_;{_+,~-|A+~nVx>AGilI0Fg@O];>vyb(w' );
define( 'NONCE_SALT',       'u+_OFLNFOi0T$7C|U*}f:.s!S(C*kc-h%jp is0x)S*JvR!-}7Q,FV)8^itLF9i^' );
define( 'WP_CACHE_KEY_SALT','X7e87k-e,0.({=D*E-I5?(Pc*,WLBI8<+x2cA.s *]Z3mPByI!`ZWYFf=-qP5juK' );

/**#@-*/

$table_prefix = 'wp_';

/* Add any custom values between this line and the “stop editing” line. */

// Enable WP debug mode and file logging
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

// Optional: log queries (for performance/analysis)
define( 'SAVEQUERIES', true );

// File system method for updates & installs.
define( 'FS_METHOD', 'direct' );

// Prevent cookie security issues
define( 'COOKIEHASH', 'e4c798fc0dd9b269d890d339bca669fd' );

// Core auto-update policy
define( 'WP_AUTO_UPDATE_CORE', 'minor' );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
