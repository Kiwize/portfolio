<?php

class Meow_Analytics_Core
{
  public $admin = null;
  public $is_rest = false;
  public $is_cli = false;
  public $site_url = null;

  public function __construct()
  {
    $this->site_url = get_site_url();
    $this->is_rest = MeowCommon_Helpers::is_rest();
    $this->is_cli = defined('WP_CLI') && WP_CLI;
    add_action('plugins_loaded', array($this, 'init'));

    if (is_admin()) {
      $access_token = get_option('mga_client_id');
      $refresh_token = get_option('mga_client_secret');
      if (!empty($refresh_token) && !empty($access_token)) {
        new Meow_Analytics_Dashboard($this);
      }
      // NOTE: Please delete this line after confirming the new settings page is worked.
      add_action('admin_notices', array($this, 'notices'));
    } else {
      // Register Google Analytics and start the tracking.
      add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
    }
  }

  function init()
  {
    load_plugin_textdomain(MGA_DOMAIN, FALSE, basename(MGA_PATH) . '/languages/');

    // Part of the core, settings and stuff
    global $mga_admin;
    $mga_admin = new Meow_Analytics_Admin();
    $this->admin = $mga_admin;

    // Only for REST
    if ($this->is_rest) {
      new Meow_Analytics_Rest($this, $this->admin);
    }
  }

  function notices()
  {
    $message = get_transient('mga_message');
    $error = get_transient('mga_error');
    if ($message) {
?>
      <div class="notice notice-success">
        <p><?= $message ?></p>
      </div>
    <?php
      delete_transient('mga_message');
    }
    if ($error) {
    ?>
      <div class="notice notice-error">
        <p>An error was received: <?= $error ?></p>
      </div>
<?php
      delete_transient('mga_error');
    }
  }

  function wp_enqueue_scripts()
  {
    $disabled = get_option( 'mga_disable_tracking', false );
    if ( $disabled ) { return; }
    $track_users = get_option( 'mga_track_logged_users', false );
    if ( !$track_users && is_user_logged_in() ) { return; }
    $track_power_users = get_option( 'mga_track_power_users', false );
    $is_power_user = current_user_can( 'editor' ) || current_user_can( 'administrator' );
    if ( !$track_power_users && $is_power_user ) { return; }
    $mga_tracking_id = get_option( 'mga_tracking_id' );
    $mga_tracking_ids = get_option( 'mga_tracking_ids' );
    if ( empty( $mga_tracking_id ) ) { return; }
    add_filter( 'script_loader_tag', array( $this, 'script_loader_tag' ), 10, 2 );
    wp_register_script( 'meow-analytics-ga',
      'https://www.googletagmanager.com/gtag/js?id=' . $mga_tracking_id, [], null, true );
    wp_enqueue_script( 'meow-analytics-ga' );
    wp_add_inline_script( 'meow-analytics-ga', $this->build_js( $mga_tracking_id, $mga_tracking_ids ) );
  }

  function build_js( $mga_tracking_id, $mga_tracking_ids )
  {
    $extra_ids = json_encode( $mga_tracking_ids ? explode( ',', $mga_tracking_ids ) : [] );
    return "
      var extra_ids = {$extra_ids};
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '${mga_tracking_id}');
      for (var i = 0; i < extra_ids.length; i++) {
        gtag('config', extra_ids[i]);
      }
    ";
  }

  function script_loader_tag($tag, $handle)
  {
    if ($handle === 'meow-analytics-ga') {
      $tag = str_replace('<script src=', '<script async src=', $tag);
      return $tag;
    }
    return $tag;
  }
}

?>