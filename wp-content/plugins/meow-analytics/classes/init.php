<?php
if ( class_exists( 'Meow_Analytics_Core' ) ) {
	function mfrh_admin_notices() {
		echo '<div class="error"><p>Thanks for installing Analytics :) However, another version is still enabled. Please disable or uninstall it.</p></div>';
	}
	add_action( 'admin_notices', 'mfrh_admin_notices' );
	return;
}

spl_autoload_register(function ( $class ) {
  $necessary = true;
  $file = null;
  if ( strpos( $class, 'Meow_Analytics' ) !== false ) {
    $file = MGA_PATH . '/classes/' . str_replace( 'meow_analytics_', '', strtolower( $class ) ) . '.php';
  }
  else if ( strpos( $class, 'MeowCommon_' ) !== false ) {
    $file = MGA_PATH . '/common/' . str_replace( 'meowcommon_', '', strtolower( $class ) ) . '.php';
  }
  else if ( strpos( $class, 'MeowPro_Analytics' ) !== false ) {
    $necessary = false;
    $file = MGA_PATH . '/premium/' . str_replace( 'meowpro_analytics_', '', strtolower( $class ) ) . '.php';
  }
  if ( $file ) {
    if ( !$necessary && !file_exists( $file ) ) {
      return;
    }
    require( $file );
  }
});

$mga_core = new Meow_Analytics_Core();

?>