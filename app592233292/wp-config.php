<?php

define('FS_METHOD', 'direct');
define('DISALLOW_FILE_EDIT', true);
define('WP_HOME', 'http://versoehnungsgemeinde.apps-1and1.net/');
define('WP_SITEURL', 'http://versoehnungsgemeinde.apps-1and1.net/');



































// ** MySQL settings ** //
/** The name of the database for WordPress */
define('DB_NAME', 'db592233353');

/** MySQL database username */
define('DB_USER', 'dbo592233353');

/** MySQL database password */
define('DB_PASSWORD', 'V2CNETM1');

/** MySQL hostname */
define('DB_HOST', 'db3065.1und1.de:3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

define('AUTH_KEY',         ';|?/RAm/+Oje76@a`1C/V|lVWSvA^t90QSF%kA,#+ C^@;}kU;~>^!i4FKX=]xwl');
define('SECURE_AUTH_KEY',  ';o?3|*Q1&3mw)+]Ug0SID$?-5V~0xT/.<pBR28{=(d=W8sA~H?^D/i^I!2}FcuS%');
define('LOGGED_IN_KEY',    'BZlWg{grOut$bQ6%9[8Yt#d03>zxG+?&F -<:q:Z6!)^:f>+|wS!uv:jn*|k-lwT');
define('NONCE_KEY',        'KHlSc&{h^9#(bp#JONLFtO+qAw5sGTZ8KK7g|cKk362++ok&[!>-`+S/!N/R >z>');
define('AUTH_SALT',        '`|^j$b|{AB7$) g8|t)U:!S!K|r3M<<3NU9NX(>M4g0w(?w(j%{~FFhyv,s|OPY0');
define('SECURE_AUTH_SALT', '>S+@^97V~suBnob$rPrLM8}6D`MK,KDOq>:u)H<7ans w0&b8zDa4EtZ87UtNWbF');
define('LOGGED_IN_SALT',   'nqn~K}W>8K&4x=t`bF&Dpu9zN.<OTnH>MJDc}7cZhGNAjN1x@foNb N4&[z0^:AQ');
define('NONCE_SALT',       '`&a!)Ran[2x[Po2W@sT7NDZ -;4r%fvCHLo/Ab$kAZ>F!Y6@}I,<XR?8?%-Zv?7B');


$table_prefix = 'dbnwz843e4';


/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/**
 * Disable the Plugin and Theme Editor.
 *
 * Occasionally you may wish to disable the plugin or theme editor to prevent
 * overzealous users from being able to edit sensitive files and potentially crash the site.
 * Disabling these also provides an additional layer of security if a hacker
 * gains access to a well-privileged user account.
 */



/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
