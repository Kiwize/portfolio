<?php
/*
Plugin Name: Meow Analytics
Plugin URI: https://meowapps.com
Description: Adds the Google Analytics (GA4 included) code to your website and a little dashboard with realtime and historical data. 
Version: 1.1.2
Author: Jordy Meow
Text Domain: meow-analytics
Domain Path: /languages

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html
*/

if ( !defined( 'MGA_VERSION' ) ) {
  define( 'MGA_VERSION', '1.1.2' );
  define( 'MGA_PREFIX', 'mga' );
  define( 'MGA_DOMAIN', 'meow-analytics' );
  define( 'MGA_ENTRY', __FILE__ );
  define( 'MGA_PATH', dirname( __FILE__ ) );
  define( 'MGA_URL', plugin_dir_url( __FILE__ ) );
}

require_once( 'classes/init.php' );

?>
