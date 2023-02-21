<?php

class Meow_Analytics_Admin extends MeowCommon_Admin {

	private $auth = null;
	private $client_id = null;
	private $client_secret = null;
	private $is_linked = true;

	public function __construct() {
		parent::__construct( MGA_PREFIX, MGA_ENTRY, MGA_DOMAIN );
		add_action( 'admin_menu', array( $this, 'app_menu' ) );
		$this->auth = new Meow_Analytics_Auth();
		$this->client_id = get_option( 'mga_client_id' );
		$this->client_secret = get_option( 'mga_client_secret' );
		$this->is_linked = !empty( get_option( 'mga_access_token' ) ) && !empty( get_option( 'mga_refresh_token' ) ) 
			&& !empty( $this->client_id ) && !empty( $this->client_secret );

		// Load the scripts only if they are needed by the current screen
		$page = isset( $_GET["page"] ) ? $_GET["page"] : null;
		$is_mga_screen = in_array( $page, [ 'mga_settings' ] );
		if ( $is_mga_screen ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}
	}

	function admin_enqueue_scripts() {

		// Load the scripts
		$physical_file = MGA_PATH . '/app/index.js';
		$cache_buster = file_exists( $physical_file ) ? filemtime( $physical_file ) : MGA_VERSION;
		wp_register_script( 'mga_meow_analytics-vendor', MGA_URL . 'app/vendor.js',
			['wp-element', 'wp-i18n'], $cache_buster
		);
		wp_register_script( 'mga_meow_analytics', MGA_URL . 'app/index.js',
			['mga_meow_analytics-vendor', 'wp-i18n'], $cache_buster
		);
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'mga_meow_analytics', 'meow-analytics' );
		}
		wp_enqueue_script('mga_meow_analytics' );

		// Load the fonts
		wp_register_style( 'meow-neko-ui-lato-font', 
			'//fonts.googleapis.com/css2?family=Lato:wght@100;300;400;700;900&display=swap');
		wp_enqueue_style( 'meow-neko-ui-lato-font' );

		// Localize and options
		wp_localize_script( 'mga_meow_analytics', 'mga_meow_analytics', array_merge( [
			'api_url' => get_rest_url(null, '/meow-analytics/v1/'),
			'rest_url' => get_rest_url(),
			'plugin_url' => MGA_URL,
			'prefix' => MGA_PREFIX,
			'domain' => MGA_DOMAIN,
			'is_pro' => class_exists( 'MeowPro_Analytics_Core' ),
			'is_registered' => !!$this->is_registered(),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'site_url' => get_site_url(),
			'redirect_url' => $this->auth->getRedirectURL(),
			'auth_url' => $this->auth->getAuthURL(),
		], $this->get_all_options() ) );
	}

	function get_all_options() {
		$message = get_transient('mga_message');
        $error = get_transient('mga_error');

		$all_options = [
			'mga_tracking_id' => get_option( 'mga_tracking_id' ),
			'mga_tracking_ids' => get_option( 'mga_tracking_ids' ),
			'mga_disable_tracking' => get_option( 'mga_disable_tracking', false ),
			'mga_track_logged_users' => get_option( 'mga_track_logged_users', false ),
			'mga_track_power_users' => get_option( 'mga_track_power_users', false ),
			'mga_client_id' => get_option( 'mga_client_id' ),
			'mga_client_secret' => get_option( 'mga_client_secret' ),
			'mga_is_linked' => !empty( get_option( 'mga_access_token' ) ) && !empty( get_option( 'mga_refresh_token' ) ) 
			&& !empty( $this->client_id ) && !empty( $this->client_secret ),
			'mga_message' => $message,
			'mga_error' => $error,
		];
		if ($message) {
			delete_transient('mga_message');
		}
		if ($error) {
			delete_transient('mga_error');
		}

		return $all_options;
	}

	function common_url( $file ) {
		return trailingslashit( plugin_dir_url( __FILE__ ) ) . 'common/' . $file;
	}

	function app_menu() {
		add_submenu_page( 'meowapps-main-menu', 'Analytics', 'Analytics', 'manage_options',
			'mga_settings', array( $this, 'admin_settings' ) );
	}

	function display_status() {
		if ( empty( $this->client_id ) || empty( $this->client_secret ) )
			$this->display_status_idle();
		else if ( !$this->is_linked )
			$this->display_status_ready();
		else
			$this->display_status_connected();
	}

	function display_status_idle() {
		?>
		<p><?= __( "If you would like to view your Google Analytics data from your WordPress Dashboard, you will need to link this website with Google Analytics. If you don't know how to do this, <a target='_blank' href='https://meowapps.com/plugin/meow-analytics/'>check the tutorial</a>.", 'meow-analytics' ) ?></p>
		<?php
	}

	function display_status_ready() {
		?>
		<div style="border: solid 2px #90cbfb; padding: 2px 10px;">
			<p><?= __( "<b>Meow Analytics is ready to link your website to Google Analytics through your Client ID.<br /></b>In the settings of <a target='_blank' href='https://console.developers.google.com/apis/credentials'>your Client ID</a>, make sure you have enabled the Google Analytics API in <i>Library</i>, and that following settings are set accordingly.", 'meow-analytics' ) ?></p>
			<p>
				<?= __( 'Authorized JavaScript origins:', 'meow-analytics' ) ?><br />
				<b><?= get_site_url() ?></b><br /><br />
				<?= __( 'Authorized redirect URIs:', 'meow-analytics' ) ?><br />
				<b><?= $this->auth->getRedirectURL() ?></b>
			</p>
			<a class='button button-primary' style='margin-bottom: 10px;' href='<?= $this->auth->getAuthURL() ?>'>
				<?= __( 'Link this website with Google Analytics', 'meow-analytics' ) ?>
			</a>
		</div>
		<?php
	}

	function display_status_connected() {
		?>
		<p><?= __( 'Everything is well :) Your website is connected to the Google Analytics API.', 'meow-analytics' ) ?></p>
		<div class="submit" style="display: flex; justify-content: flex-end;">
			<form method='post' action='<?= $this->auth->getRedirectURL() ?>'>
				<input type='hidden' name='action' value='refresh'></input>
				<input style="margin-right: 10px;" type='submit' 
					value='<?= __( 'Refresh Token', 'meow-analytics' ) ?>' class='button'></input>
			</form>
			<form method='post' action='<?= $this->auth->getRedirectURL() ?>'>
				<input type='hidden' name='action' value='unlink'></input>
				<input type='submit' value='Unlink' class='button'></input>
			</form>
	</div>
		<?php
	}

	function admin_settings() {
		echo '<div id="meow-analytics-settings"></div>';
	}

	/*
		OPTIONS CALLBACKS
	*/

	function admin_tracking_id_callback() {
		$value = get_option( 'mga_tracking_id' );
		$html = '<input type="text" placeholder="UA-XXXXXXXX-X" style="width: 100%;" id="mga_tracking_id" name="mga_tracking_id" value="' . $value . '" />';
		echo $html;
	}

	function admin_disable_tracking_callback() {
		$value = get_option( 'mga_disable_tracking', false );
		$html = '<input type="checkbox" id="mga_disable_tracking" name="mga_disable_tracking" value="1" ' . checked( 1, $value, false ) . '/>';
		$html .= '<label>' . __( 'Enable', 'meow-analytics' ) . '</label><br /><small>' . __( 'Disable the tracking completely. The features related to the dashboard can still be used.', 'meow-analytics' ) . '</small>';
		echo $html;
	}

	function admin_track_logged_users_callback() {
		$value = get_option( 'mga_track_logged_users', false );
		$html = '<input type="checkbox" id="mga_track_logged_users" name="mga_track_logged_users" value="1" ' . checked( 1, $value, false ) . '/>';
		$html .= '<label>' . __( 'Enable', 'meow-analytics' ) . '</label><br /><small>' . __( 'The logged-in users will also be tracked by Google Analytics.', 'meow-analytics' ) . '</small>';
		echo $html;
	}

	function admin_client_id_callback() {
		$value = get_option( 'mga_client_id' );
		$html = '<input type="text" style="width: 100%;"' . ( $this->is_linked ? ' disabled ' : '' ) .
			'placeholder="XXXXX-XXXX.apps.googleusercontent.com" id="mga_client_id" name="mga_client_id" value="' . $value . '" />';
		echo $html;
	}

	function admin_client_secret_callback() {
		$value = get_option( 'mga_client_secret' );
		$html = '<input type="text" style="width: 100%;"' . ( $this->is_linked ? ' disabled ' : '' ) .
			'placeholder="" id="mga_client_secret" name="mga_client_secret" value="' . $value . '" />';
		echo $html;
	}

	// delete these later
	function display_ads() {
		return !get_option( 'meowapps_hide_ads', false );
	}
	function display_title( $title = "Meow Apps",
		$author = "By <a style='text-decoration: none;' href='https://meowapps.com' target='_blank'>Jordy Meow</a>" ) {
		if ( !empty( $this->prefix ) && $title !== "Meow Apps" )
			$title = apply_filters( $this->prefix . '_plugin_title', $title );
		if ( $this->display_ads() ) {
		}
		?>
		<h1 style="line-height: 16px;">
			<img width="42" style="margin-right: 10px; float: left; position: relative; top: -5px;"
				src="<?php echo 'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxIiB2aWV3Qm94PSIwIDAgMTY1IDE2NSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8c3R5bGU+CiAgICAuc3Qye2ZpbGw6IzgwNDYyNX0uc3Qze2ZpbGw6I2ZkYTk2MH0KICA8L3N0eWxlPgogIDxwYXRoIGQ9Ik03MiA3YTc2IDc2IDAgMCAxIDg0IDkxQTc1IDc1IDAgMSAxIDcyIDd6IiBmaWxsPSIjNGE2YjhjIi8+CiAgPHBhdGggZD0iTTQ4IDQ4YzIgNSAyIDEwIDUgMTQgNSA4IDEzIDE3IDIyIDIwbDEtMTBjMS0yIDMtMyA1LTNoMTNjMiAwIDQgMSA1IDNsMyA5IDQtMTBjMi0zIDYtMiA5LTJoMTFjMyAyIDMgNSAzIDhsMiAzN2MwIDMtMSA3LTQgOGgtMTJjLTIgMC0zLTItNS00LTEgMS0yIDMtNCAzLTUgMS05IDEtMTMtMS0zIDItNSAyLTkgMnMtOSAxLTEwLTNjLTItNC0xLTggMC0xMi04LTMtMTUtNy0yMi0xMi03LTctMTUtMTQtMjAtMjMtMy00LTUtOC01LTEzIDEtNCAzLTEwIDYtMTMgNC0zIDEyLTIgMTUgMnoiIGZpbGw9IiMxMDEwMTAiLz4KICA8cGF0aCBjbGFzcz0ic3QyIiBkPSJNNDMgNTFsNCAxMS02IDVoLTZjLTMtNS0zLTExIDAtMTYgMi0yIDYtMyA4IDB6Ii8+CiAgPHBhdGggY2xhc3M9InN0MyIgZD0iTTQ3IDYybDMgNmMwIDMgMCA0LTIgNnMtNCAyLTcgMmwtNi05aDZsNi01eiIvPgogIDxwYXRoIGNsYXNzPSJzdDIiIGQ9Ik01MCA2OGw4IDljLTMgMy01IDYtOSA4bC04LTljMyAwIDUgMCA3LTJzMy0zIDItNnoiLz4KICA8cGF0aCBkPSJNODIgNzRoMTJsNSAxOCAzIDExIDgtMjloMTNsMiA0MmgtOGwtMS0yLTEtMzEtMTAgMzItNyAxLTktMzMtMSAyOS0xIDRoLThsMy00MnoiIGZpbGw9IiNmZmYiLz4KICA8cGF0aCBjbGFzcz0ic3QzIiBkPSJNNTggNzdsNSA1Yy0xIDQtMiA4LTcgOGwtNy01YzQtMiA2LTUgOS04eiIvPgogIDxwYXRoIGNsYXNzPSJzdDIiIGQ9Ik02MyA4Mmw5IDUtNiA5LTEwLTZjNSAwIDYtNCA3LTh6Ii8+CiAgPHBhdGggY2xhc3M9InN0MyIgZD0iTTcyIDg3bDMgMS0xIDExLTgtMyA2LTEweiIvPgo8L3N2Zz4K'; ?>"><?php echo $title; ?><br />
			<span style="font-size: 12px"><?php echo $author; ?></span>
		</h1>
		<div style="clear: both;"></div>
		<?php
	}

}

?>
