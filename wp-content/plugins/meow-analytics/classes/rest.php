<?php

class Meow_Analytics_Rest
{
	private $core = null;
	private $namespace = 'meow-analytics/v1';

	public function __construct( $core, $admin ) {
		if ( !current_user_can( 'administrator' ) ) {
			return;
		}
		$this->core = $core;
		$this->admin = $admin;
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	function rest_api_init() {
		try {
			// SETTINGS
			register_rest_route( $this->namespace, '/update_option', array(
				'methods' => 'POST',
				'callback' => array( $this, 'rest_update_option' )
			) );
			register_rest_route( $this->namespace, '/all_settings', array(
				'methods' => 'GET',
				'callback' => array( $this, 'rest_all_settings' ),
			) );
			register_rest_route( $this->namespace, '/unlink', array(
				'methods' => 'POST',
				'permission_callback' => '__return_true',
				'callback' => array( $this, 'rest_unlink' ),
			) );
			register_rest_route( $this->namespace, '/refresh_token', array(
				'methods' => 'POST',
				'permission_callback' => '__return_true',
				'callback' => array( $this, 'rest_refresh_token' ),
			) );
		}
		catch (Exception $e) {
			var_dump($e);
		}
	}

	function rest_all_settings() {
		return new WP_REST_Response( [
			'success' => true,
			'data' => $this->admin->get_all_options()
		], 200 );
	}

	function rest_update_option( $request ) {
		$params = $request->get_json_params();
		try {
			$name = $params['name'];
			$value = is_bool( $params['value'] ) ? ( $params['value'] ? '1' : '' ) : $params['value'];
			$success = update_option( $name, $value );
			if ( $success ) {
				$res = $this->validate_updated_option( $name );
				$result = $res['result'];
				$message = $res['message'];
				return new WP_REST_Response([ 'success' => $result, 'message' => $message ], 200 );
			}
			return new WP_REST_Response([ 'success' => false, 'message' => "Could not update option." ], 200 );
		}
		catch (Exception $e) {
			return new WP_REST_Response([
				'success' => false,
				'message' => $e->getMessage(),
			], 500 );
		}
	}

	function rest_unlink() {
		try {
			delete_option( 'mga_expires_at' );
			delete_option( 'mga_access_token' );
			delete_option( 'mga_refresh_token' );
			set_transient( 'mga_message', __( 'Accounts were unlinked. However, if the Tracking ID is still present, Google Analytics is still keeping track of the activity on your website.', 'meow-analytics' ), 5 );
			return new WP_REST_Response([ 'success' => true ], 200 );
		}
		catch (Exception $e) {
			return new WP_REST_Response([
				'success' => false,
				'message' => $e->getMessage(),
			], 500 );
		}
	}

	function rest_refresh_token() {
		$auth = new Meow_Analytics_Auth();
		try {
			if ( $auth->getRefreshToken() ) {
				set_transient( 'mga_message', __( 'The token was refreshed.', 'meow-analytics' ), 5 );
			}
			return new WP_REST_Response([ 'success' => true ], 200 );
		}
		catch (Exception $e) {
			return new WP_REST_Response([
				'success' => false,
				'message' => $e->getMessage(),
			], 500 );
		}
	}

	/**
	 * Private
	 */
	private function validate_updated_option( $option_name ) {
		$mga_disable_tracking = get_option( 'mga_disable_tracking', false );
		$mga_track_logged_users = get_option( 'mga_track_logged_users', false );
		$mga_track_power_users = get_option( 'mga_track_power_users', false );
		if ( $mga_disable_tracking === '' )
			update_option( 'mga_disable_tracking', false );
		if ( $mga_track_logged_users === '' )
			update_option( 'mga_track_logged_users', false );
		if ( $mga_track_power_users === '' )
			update_option( 'mga_track_power_users', false );
		return $this->createValidationResult();
	}

	private function createValidationResult( $result = true, $message = null) {
		$message = $message ? $message : __( 'OK', 'meow-analytics' );
		return ['result' => $result, 'message' => $message];
	}
}
