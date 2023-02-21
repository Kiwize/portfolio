<?php

// https://myaccount.google.com/permissions

class Meow_Analytics_Auth {

  const TOKEN_URL = 'https://www.googleapis.com/oauth2/v4/token';
	const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
	const SCOPE_URL = 'https://www.googleapis.com/auth/analytics.readonly';
  
  public function __construct() {
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'mga_settings') {
      $this->handle_auth_events();
    }
  }

  private function handle_auth_events() {
    $code = isset( $_GET['code'] ) ? $_GET['code'] : null;
    $scope = isset( $_GET['scope'] ) ? $_GET['scope'] : null;
    $action = isset( $_POST['action'] ) ? $_POST['action'] : null;
    if ( $scope === self::SCOPE_URL && !empty( $code ) ) {
      if ( $this->getAccessToken( $code ) ) {
        set_transient( 'mga_message', __( 'Accounts were linked successfully! :)', 'meow-analytics' ), 5 );
      }
    }
    // Might not need below any more. Th unlink and the refresh are handled in /rest.php.
    else if ( $action === 'unlink' ) {
      delete_option( 'mga_expires_at' );
      delete_option( 'mga_access_token' );
      delete_option( 'mga_refresh_token' );
      set_transient( 'mga_message', __( 'Accounts were unlinked. However, if the Tracking ID is still present, Google Analytics is still keeping track of the activity on your website.', 'meow-analytics' ), 5 );
    }
    else if ( $action === 'refresh' ) {
      if ( $this->getRefreshToken() ) {
        set_transient( 'mga_message', __( 'The token was refreshed.', 'meow-analytics' ), 5 );
      }
    }
  }
  
  public function getRedirectURL() {
    return admin_url( 'admin.php?page=mga_settings' );
  }

  public function getAuthURL() {
		$params = array( 
      'response_type' => 'code',
      'client_id' => get_option( 'mga_client_id' ),
      'redirect_uri' => $this->getRedirectURL(),
      'scope' => self::SCOPE_URL,
      'access_type' => 'offline',
      'prompt' => 'consent'
    );
		return self::AUTH_URL . '?' . http_build_query( $params );
  }

  private function getAccessToken( $code ) {
    $options = array( 
      'body' => array(
        'code' => $code,
        'client_id' => get_option( 'mga_client_id' ),
        'client_secret' => get_option( 'mga_client_secret' ),
        'redirect_uri' => $this->getRedirectURL(),
        'grant_type' => 'authorization_code'
      )
    );
    $result = wp_remote_post( self::TOKEN_URL, $options );
    $json = json_decode( $result['body'] );
    if ( isset( $json->error ) ) {
      set_transient( 'mga_error', $json->error, 5 );
      return false;
    }
    update_option( 'mga_expires_at', time() + $json->expires_in );
    update_option( 'mga_access_token', $json->access_token );
    update_option( 'mga_refresh_token', $json->refresh_token );
    return true;
  }

  public function getRefreshToken() {
    $options = array( 
      'body' => array(
        'client_id' => get_option( 'mga_client_id' ),
        'client_secret' => get_option( 'mga_client_secret' ),
        'refresh_token' => get_option( 'mga_refresh_token' ),
        'grant_type' => 'refresh_token'
      )
    );
    $result = wp_remote_post( self::TOKEN_URL, $options );
    // If $result is WP Error
    if ( is_wp_error( $result ) ) {
      set_transient( 'mga_error', $result->get_error_message(), 5 );
      return false;
    }
    $json = json_decode( $result['body'] );
    if ( isset( $json->error ) ) {
      set_transient( 'mga_error', $json->error, 5 );
      return false;
    }
    update_option( 'mga_expires_at', time() + $json->expires_in );
    update_option( 'mga_access_token', $json->access_token );
    return true;
  }  
}

?>