<?php
class Meow_DBCLNR_Admin extends MeowCommon_Admin {

	public $core;

	public function __construct( $core ) {
		parent::__construct( DBCLNR_PREFIX, DBCLNR_ENTRY, DBCLNR_DOMAIN, class_exists( 'MeowPro_DBCLNR_Core' ) );
		$this->core = $core;
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'app_menu' ) );

			// Load the scripts only if they are needed by the current screen
			$page = isset( $_GET["page"] ) ? sanitize_text_field( $_GET["page"] ) : null;
			$is_dbclnr_screen = in_array( $page, [ 'dbclnr_settings', 'dbclnr_dashboard' ] );
			$is_meowapps_dashboard = $page === 'meowapps-main-menu';
			if ( $is_meowapps_dashboard || $is_dbclnr_screen ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			}
		}
	}

	function admin_enqueue_scripts() {

		// Load the scripts
		$physical_file = DBCLNR_PATH . '/app/index.js';
		$cache_buster = file_exists( $physical_file ) ? filemtime( $physical_file ) : DBCLNR_VERSION;
		wp_register_script( 'dbclnr_database_cleaner-vendor', DBCLNR_URL . 'app/vendor.js',
			['wp-element', 'wp-i18n'], $cache_buster
		);
		wp_register_script( 'dbclnr_database_cleaner', DBCLNR_URL . 'app/index.js',
			['dbclnr_database_cleaner-vendor', 'wp-i18n'], $cache_buster
		);
		wp_set_script_translations( 'dbclnr_database_cleaner', 'database-cleaner' );
		wp_enqueue_script('dbclnr_database_cleaner' );

		// Load the fonts
		wp_register_style( 'meow-neko-ui-lato-font', '//fonts.googleapis.com/css2?family=Lato:wght@100;300;400;700;900&display=swap');
		wp_enqueue_style( 'meow-neko-ui-lato-font' );

		// Localize and options
		wp_localize_script( 'dbclnr_database_cleaner', 'dbclnr_database_cleaner', [
			'api_url' => rest_url( 'database-cleaner/v1' ),
			'rest_url' => rest_url(),
			'plugin_url' => DBCLNR_URL,
			'prefix' => DBCLNR_PREFIX,
			'db_prefix' => $this->core->prefix,
			'domain' => DBCLNR_DOMAIN,
			'is_pro' => class_exists( 'MeowPro_DBCLNR_Core' ),
			'is_registered' => !!$this->is_registered(),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'core' => [
				'posts' => $this->core->add_clean_style_data( Meow_DBCLNR_Items::$POSTS ),
				'posts_metadata' => $this->core->add_clean_style_data( Meow_DBCLNR_Items::$POSTS_METADATA ),
				'users' => $this->core->add_clean_style_data( Meow_DBCLNR_Items::$USERS ),
				'comments' => $this->core->add_clean_style_data( Meow_DBCLNR_Items::$COMMENTS ),
				'transients' => $this->core->add_clean_style_data( Meow_DBCLNR_Items::$TRANSIENTS ),
			],
			'core_count' => [
				'posts' => $this->core->get_core_entry_counts( Meow_DBCLNR_Items::$POSTS ),
				'posts_metadata' => $this->core->get_core_entry_counts( Meow_DBCLNR_Items::$POSTS_METADATA ),
				'users' => $this->core->get_core_entry_counts( Meow_DBCLNR_Items::$USERS ),
				'comments' => $this->core->get_core_entry_counts( Meow_DBCLNR_Items::$COMMENTS ),
				'transients' => $this->core->get_core_entry_counts( Meow_DBCLNR_Items::$TRANSIENTS ),
			],
			'options' => $this->core->get_all_options(),
		] );
	}

	function is_registered() {
		return apply_filters( DBCLNR_PREFIX . '_meowapps_is_registered', false, DBCLNR_PREFIX );
	}

	function app_menu() {
		add_submenu_page( 'meowapps-main-menu', 'Database Cleaner', 'Database Cleaner', 'manage_options',
			'dbclnr_settings', array( $this, 'admin_settings' ) );
	}

	function admin_settings() {
		echo wp_kses_post( '<div id="dbclnr-admin-settings"></div>' );
	}

	function get_db_tables() {
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare( "
			SELECT TABLE_NAME 'table'
			FROM information_schema.TABLES
			WHERE table_schema = %s
			",
			DB_NAME
		), ARRAY_A );

		return $results;
	}

	function get_options() {
		global $wpdb;
		$where = "WHERE option_name NOT LIKE '_transient_%' AND option_name NOT LIKE '_site_transient_%' ";
		$result = $wpdb->get_results( "
				SELECT option_name, length(option_value) AS option_value_length, autoload
				FROM $wpdb->options
				$where
				ORDER BY option_value_length DESC
				", ARRAY_A);

		return $result;
	}

	function get_option_value( $option_name ) {
		global $wpdb;
		$result = $wpdb->get_results( $wpdb->prepare( "
				SELECT option_value
				FROM $wpdb->options
				WHERE option_name = %s
			", $option_name ) );
		return $result;
	}

	function delete_options( $option_names ) {
		global $wpdb;
		$placeholder = array_fill( 0, count( $option_names ), '%s' );
		$placeholder = implode( ', ', $placeholder );
		$result = $wpdb->query( $wpdb->prepare( "
			DELETE t
			FROM $wpdb->options t
			WHERE option_name IN ($placeholder)
		", $option_names ) );

		if ($result === false) {
			throw new Error('Failed to delete the autoloaded options:' . $wpdb->last_error);
		}
		return $result;
	}

	function switch_autoloaded_option( $option_name, $autoload ) {
		global $wpdb;
		$result = $wpdb->query( $wpdb->prepare( "
			UPDATE $wpdb->options
			SET autoload = %s
			WHERE option_name = %s
		", $autoload, $option_name ) );

		return $result;
	}

	function delete_crons( $crons ) {
		foreach ( $crons as $cron ) {
			$result = $this->core->remove_cron_entry( $cron['name'], $cron['args'] );
			if ( $result === false ) {
				throw new Error('Failed to delete the cron option: ' . $cron['name'] );
			}
		}
		return true;
	}

	function delete_table( $table_name ) {
		global $wpdb;
		$result = $wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`;" );
		if ($result === false) {
			error_log('PHP Exception: ' . $wpdb->last_error);
		}
		return $result;
	}

	function optimize_table( $table_name ) {
		global $wpdb;
		$result = $wpdb->query( "OPTIMIZE TABLE `{$table_name}`;" );
		if ($result === false) {
			error_log('PHP Exception: ' . $wpdb->last_error);
		}
		return $result;
	}

	function repair_table( $table_name ) {
		global $wpdb;
		$result = $wpdb->query( "OPTIMIZE TABLE `{$table_name}`;" );
		if ($result === false) {
			error_log('PHP Exception: ' . $wpdb->last_error);
		}
		return $result;
	}

	function valid_item_operation( $item, $is_auto_clean = false ) {
		$clean_style = $this->core->get_option( $item . '_clean_style' );
		if ( $clean_style === 'never' ) {
			return false;
		}
		return !$is_auto_clean || ($is_auto_clean && $clean_style === 'auto');
	}

	function valid_custom_query_operation( $clean_style, $is_auto_clean = false ) {
		if ( !$clean_style || $clean_style === 'never') {
			return false;
		}

		return !$is_auto_clean || ($is_auto_clean && $clean_style === 'auto');
	}

	function valid_table_name( $table_name ) {
		$tables = array_column( $this->get_db_tables(), 'table' );
		return in_array( $table_name, $tables, true );
	}

	function valid_deletable_table_name( $table_name ) {
		$data = apply_filters( 'dbclnr_check_table_info', $this->core->prefix . $table_name, null );
		return strtolower($data['usedBy']) !== 'wordpress';
	}

	function valid_deletable_option_name( $option_name ) {
		$data = apply_filters( 'dbclnr_check_option_info', $option_name, null );
		return strtolower($data['usedBy']) !== 'wordpress';
	}

	function valid_deletable_cron_name( $option_name ) {
		$data = apply_filters( 'dbclnr_check_cron_info', $option_name, null );
		return strtolower($data['usedBy']) !== 'wordpress';
	}
}

?>