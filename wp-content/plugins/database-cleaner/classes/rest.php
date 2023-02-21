<?php

class Meow_DBCLNR_Rest
{
	private $core = null;
	private $namespace = 'database-cleaner/v1';
	private $customQueryItemPrefix = 'cq-';

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
			register_rest_route( $this->namespace, '/update_options', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_update_options' )
			) );
			register_rest_route( $this->namespace, '/all_settings', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_all_settings' ),
			) );
			register_rest_route( $this->namespace, '/db_sizes', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_db_sizes' ),
			) );
			register_rest_route( $this->namespace, '/reset_options', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_features' ),
				'callback' => array( $this, 'rest_reset_options' )
			) );
			register_rest_route( $this->namespace, '/total_db_size', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_total_db_size' )
			) );
			// Posts Tables
			register_rest_route( $this->namespace, '/list_post_types', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_list_post_types' ),
			) );
			register_rest_route( $this->namespace, '/posts', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_posts' ),
			) );
			register_rest_route( $this->namespace, '/posts_metadata', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_posts_metadata' ),
			) );
			// Users Tables
			register_rest_route( $this->namespace, '/users', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_users' ),
			) );
			// Comments Tables
			register_rest_route( $this->namespace, '/comments', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_comments' ),
			) );
			// Options Tables
			register_rest_route( $this->namespace, '/transients', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_transients' ),
			) );
			register_rest_route( $this->namespace, '/options', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_options' ),
			) );
			register_rest_route( $this->namespace, '/option_value', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_option_value' ),
				'args' => array(
					'option_name' => array( 'required' => true ),
				)
			) );
			register_rest_route( $this->namespace, '/delete_options', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_delete_options' ),
			) );
			register_rest_route( $this->namespace, '/switch_autoloaded_option', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_switch_autoloaded_option' ),
			) );
			register_rest_route( $this->namespace, '/delete_crons', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_delete_crons' ),
			) );

			register_rest_route( $this->namespace, '/entry_count', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_entry_count' ),
				'args' => array(
					'item' => array( 'required' => true ),
				)
			) );
			register_rest_route( $this->namespace, '/delete_entries', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_delete_entries' ),
			) );
			register_rest_route( $this->namespace, '/delete_tables', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_delete_tables' ),
			) );
			register_rest_route( $this->namespace, '/optimize_tables', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_optimize_tables' ),
			) );
			register_rest_route( $this->namespace, '/repair_tables', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_repair_tables' ),
			) );
			register_rest_route( $this->namespace, '/table', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_table' ),
			) );
			register_rest_route( $this->namespace, '/custom_query_count', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_custom_query_count' ),
				'args' => array(
					'item' => array( 'required' => true ),
				)
			) );
			register_rest_route( $this->namespace, '/custom_query_delete', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_custom_query_delete' ),
			) );
			register_rest_route( $this->namespace, '/entries', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_entries' ),
				'args' => array(
					'item' => array( 'required' => true ),
				)
			) );

			// Cron Jobs
			register_rest_route( $this->namespace, '/cron_jobs', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_cron_jobs' ),
			) );

			// LOGS
			register_rest_route( $this->namespace, '/log_db_size', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_features' ),
				'callback' => array( $this, 'rest_log_db_size' )
			) );
			register_rest_route( $this->namespace, '/refresh_logs', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_features' ),
				'callback' => array( $this, 'rest_refresh_logs' )
			) );
			register_rest_route( $this->namespace, '/clear_logs', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_features' ),
				'callback' => array( $this, 'rest_clear_logs' )
			) );

			// Auto Clean
			register_rest_route( $this->namespace, '/auto_clean_items', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_auto_clean_items' ),
			) );

			// Plugins
			register_rest_route( $this->namespace, '/plugins', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_plugins' ),
			) );

			// Generate Fake Data
			register_rest_route( $this->namespace, '/generate_fake_data', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_features' ),
				'callback' => array( $this, 'rest_generate_fake_data' )
			) );
		}
		catch (Exception $e) {
			var_dump($e);
		}
	}

	function rest_plugins() {
		return new WP_REST_Response( [
			'success' => true,
			'data' => $this->get_installed_plugins(),
		], 200 );
	}

	function rest_all_settings() {
		return new WP_REST_Response( [
			'success' => true,
			'data' => $this->core->get_all_options(),
		], 200 );
	}

	function rest_db_sizes() {
		$db_sizes = $this->core->get_tables_size();
		$data = $this->add_table_info_data($db_sizes);
		return new WP_REST_Response( [
			'success' => true,
			'data' => $data,
		], 200 );
	}

	function rest_reset_options() {
		$this->core->reset_options();
		return new WP_REST_Response( [ 'success' => true, 'data' => $this->core->get_all_options() ], 200 );
	}

	function rest_cron_jobs() {
		$cron_jobs = get_option( 'cron' );
		$data = $this->add_cron_info( $this->core->format_cron_info( $cron_jobs ) );
		return new WP_REST_Response( [
			'success' => true,
			'data' => $data,
			'total' => count($data),
		], 200 );
	}

	function rest_log_db_size() {
		$total_size = $this->core->get_database_size();
		$this->core->update_database_size( $total_size );
		$this->core->log("🏁 The total size of your database is {$total_size} MB.");
		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	function rest_total_db_size() {
		$this->core->refresh_database_size();
		return new WP_REST_Response( [ 
			'success' => true,
			'data' => $this->core->get_all_options(),
		], 200 );
	}

	function rest_update_options( $request ) {
		try {
			$params = $request->get_json_params();
			$value = $params['options'];
			$options = $this->core->update_options( $value );
			$success = !!$options;
			$message = __( $success ? 'OK' : "Could not update options.", 'database-cleaner' );
			return new WP_REST_Response([ 'success' => $success, 'message' => $message, 'options' => $options ], 200 );
		}
		catch (Exception $e) {
			return new WP_REST_Response([
				'success' => false,
				'message' => $e->getMessage(),
			], 500 );
		}
	}

	function validate_updated_option( $option_name ) {
		return $this->create_validation_result();
	}

	function create_validation_result( $result = true, $message = null) {
		$message = $message ? $message : __( 'OK', 'database-cleaner' );
		return ['result' => $result, 'message' => $message];
	}

	function rest_list_post_types() {
		$list_post_types = $this->core->get_post_types();
		$list = array();
		foreach ( $list_post_types as $post_type ) {
			$list[] = [
				'item' => 'list_post_types_' . $post_type, 'name' => $post_type
			];
		}
		$data = $this->core->add_clean_style_data( $list );
		$data = $this->add_post_type_info_data( $data );
		return new WP_REST_Response( [
			'success' => true,
			'data' => $data
		], 200 );
	}

	function rest_posts() {
		$data = $this->core->add_clean_style_data( Meow_DBCLNR_Items::$POSTS );
		return new WP_REST_Response( [
			'success' => true,
			'data' => $data,
		], 200 );
	}

	function rest_posts_metadata() {
		$data = $this->core->add_clean_style_data( Meow_DBCLNR_Items::$POSTS_METADATA );
		return new WP_REST_Response( [
			'success' => true,
			'data' => $data,
		], 200 );
	}

	function rest_users() {
		$data = $this->core->add_clean_style_data( Meow_DBCLNR_Items::$USERS );
		return new WP_REST_Response( [
			'success' => true,
			'data' => $data,
		], 200 );
	}

	function rest_comments() {
		$data = $this->core->add_clean_style_data( Meow_DBCLNR_Items::$COMMENTS );
		return new WP_REST_Response( [
			'success' => true,
			'data' => $data,
		], 200 );
	}

	function rest_transients() {
		$data = $this->core->add_clean_style_data( Meow_DBCLNR_Items::$TRANSIENTS );
		return new WP_REST_Response( [
			'success' => true,
			'data' => $data,
		], 200 );
	}

	function rest_auto_clean_items() {
		return new WP_REST_Response( [
			'success' => true,
			'data' => $this->get_auto_clean_items(),
		], 200 );
	}

	function rest_options() {
		$data = $this->admin->get_options();
		$data = $this->add_option_info( $data );
		return new WP_REST_Response( [
			'success' => true,
			'data' => $data,
		], 200 );
	}

	function rest_option_value( $request ) {
		$option_name = sanitize_text_field( $request->get_param('option_name') );
		if ( !$option_name ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Missing the option name parameter.' ], 400 );
		}
		//$data = $this->admin->get_option_value( $option_name );

		return new WP_REST_Response( [
			'success' => true,
			'data' => get_option( $option_name ),
		], 200 );
	}

	function rest_delete_options( $request ) {
		$params = $request->get_json_params();
		$option_name = isset( $params['item'] ) ? [ $params['item'] ] : null;
		$option_names = isset( $params['items'] ) ? $params['items'] : null;
		$option_names = $option_name ?? $option_names;
		if ( !$option_names ) {
			return new WP_REST_Response( [
				'success' => false,
				'message' => 'Missing an option name parameter.',
				'data' => $option_names ?? [],
			], 400 );
		}
		foreach ( $option_names as $option_name ) {
			$invalid_option_names = null;
			if ( !$this->admin->valid_deletable_option_name( $option_name ) ) {
				$invalid_option_names[] = $option_name;
			}
			if ( $invalid_option_names ) {
				return new WP_REST_Response( [
					'success' => false,
					'message' => 'Can not delete the options : ' . implode( ', ', $invalid_option_names ),
					'data' => $option_names,
				], 400 );
			}
		}
		try {
			$result = $this->admin->delete_options( $option_names );
			foreach ( $option_names as $name ) {
				$this->core->log("✅ Deleted option '{$name}'");
			}
			return new WP_REST_Response( [
				'success' => true,
				'data' => [
					'deleted' => $result,
					'finished' => $this->is_finished( $result ),
					'data' => [],
				],
			], 200 );
		}
		catch (Exception $e) {
			return new WP_REST_Response([
				'success' => false,
				'message' => $e->getMessage(),
				'data' => $option_names,
			], 500 );
		}
	}

	function rest_switch_autoloaded_option( $request ) {
		$params = $request->get_json_params();
		$option_name = isset( $params['item'] ) ? $params['item'] : null;
		$autoload = isset( $params['autoload'] ) ? $params['autoload'] : null;
		if ( !$option_name || !$autoload ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Missing an option name or autoload value parameter.' ], 400 );
		}
		$data = $this->admin->switch_autoloaded_option( $option_name, $autoload );
		return new WP_REST_Response( [
			'success' => true,
			'data' => $data,
		], 200 );
	}

	function rest_delete_crons( $request ) {
		$params = $request->get_json_params();
		$cron = isset( $params['item'] ) ? [ $params['item'] ] : null;
		$crons = isset( $params['items'] ) ? $params['items'] : null;
		$crons = $cron ?? $crons;
		if ( !$crons ) {
			return new WP_REST_Response( [
				'success' => false,
				'message' => 'Missing a cron name parameter.',
				'data' => $crons ?? [],
			], 400 );
		}
		foreach ( $crons as $cron ) {
			$invalid_cron_names = null;
			if ( !$this->admin->valid_deletable_cron_name( $cron['name'] ) ) {
				$invalid_cron_names[] = $cron['name'];
			}
			if ( $invalid_cron_names ) {
				return new WP_REST_Response( [
					'success' => false,
					'message' => 'Can not delete the crons: ' . implode( ', ', $invalid_cron_names ),
					'data' => $crons,
				], 400 );
			}
		}
		try {
			$result = $this->admin->delete_crons( $crons );
			foreach ( $crons as $cron ) {
				$this->core->log("✅ Deleted cron '{$cron['name']}'");
			}
			return new WP_REST_Response( [
				'success' => true,
				'data' => [
					'deleted' => $result,
					'finished' => $this->is_finished( $result ),
					'data' => [],
				],
			], 200 );
		}
		catch (Exception $e) {
			return new WP_REST_Response([
				'success' => false,
				'message' => $e->getMessage(),
				'data' => $crons,
			], 500 );
		}
	}

	function rest_entry_count( $request ) {
		$item = sanitize_text_field( $request->get_param('item') );
		if ( !$item ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Missing item parameters.' ], 400 );
		}
		$data = $this->get_entry_count( $item );
		if ( $data === false ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'This item does not exists.' ], 400 );
		}
		return new WP_REST_Response( [
			'success' => true,
			'data' => $data,
		], 200 );
	}

	function rest_entries( $request ) {
		$item = sanitize_text_field( $request->get_param('item') );
		$offset = sanitize_text_field( $request->get_param('offset') ) ?? 0;
		if ( !$item ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Missing item parameters.' ], 400 );
		}
		$age_threshold = $this->core->get_option( 'aga_threshold' );
		$age_threshold = $age_threshold === 'none' ? 0 : $age_threshold;
		if ( array_key_exists( $item, Meow_DBCLNR_Queries::$GET ) ) {
			$queries = new Meow_DBCLNR_Queries();
			return new WP_REST_Response( [
				'success' => true,
				'data' => $queries->query_get( $item, $offset, $age_threshold ),
			], 200 );
		}
		$post_type = null;
		$post_status = null;
		$param = $this->get_item_param($item);
		if ( !$param ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'This item does not exists.' ], 400 );
		}
		${$param['var']} = $param['value'];
		return new WP_REST_Response( [
			'success' => true,
			'data' => $this->core->get_entries( $post_type, $post_status, $age_threshold, $offset ),
		], 200 );
	}

	function rest_delete_entries( $request ) {
		$params = $request->get_json_params();
		$item = isset( $params['item'] ) ? $params['item'] : null;
		$is_auto_clean = isset( $params['is_auto_clean'] ) ? $params['is_auto_clean'] : false;
		if ( !$item ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Missing item parameters.' ], 400 );
		}
		if ( !$this->admin->valid_item_operation( $item, $is_auto_clean ) ) {
			return new WP_REST_Response( [
				'success' => false,
				'message' => 'Cannot delete this entry due to its clean style.'
			], 400 );
		}
		$age_threshold = $this->core->get_option( 'aga_threshold' );
		$age_threshold = $age_threshold === 'none' ? 0 : $age_threshold;
		try {
			if ( array_key_exists( $item, Meow_DBCLNR_Queries::$QUERIES ) ) {
				$queries = new Meow_DBCLNR_Queries();
				$result = $queries->query_delete( $item, $age_threshold );

				// Logging
				$name = Meow_DBCLNR_Items::getName( $item ) ?? $item;
				$this->core->log("✅ Cleaned '{$name}'");

				return new WP_REST_Response( [
					'success' => true,
					'data' => [
						'deleted' => $result,
						'finished' => $this->is_finished( $result ),
					],
				], 200 );
			}

			$post_type = null;
			$post_status = null;
			$param = $this->get_item_param($item);
			if ( !$param ) {
				return new WP_REST_Response( [ 'success' => false, 'message' => 'This item does not exists.' ], 400 );
			}

			${$param['var']} = $param['value'];
			$affected = $this->core->delete_entries( $post_type, $post_status, $age_threshold );

			// Logging
			$name = Meow_DBCLNR_Items::getName( $item ) ?? $item;
			$this->core->log("✅ Emptied post type '{$name}'");

			return new WP_REST_Response( [
				'success' => true,
				'data' => [
					'deleted' => $affected,
					'finished' => $this->is_finished( $affected ),
				],
			], 200 );
		}
		catch (Exception $e) {
			return new WP_REST_Response([
				'success' => false,
				'message' => $e->getMessage(),
			], 500 );
		}
	}

	function rest_delete_tables( $request ) {
		$params = $request->get_json_params();
		$tables = isset( $params['tables'] ) ? $params['tables'] : null;
		if ( !$tables ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Missing tables parameters.' ], 400 );
		}
		$invalid_tables = [];
		foreach ( $tables as $table ) {
			// Note for Naomi: I am not sure why we need to check for the table existence here.
			// So for now, I am removing the check.
			// If we add back, we should at least cache the list of tables though :)
			
			// if ( !$this->admin->valid_table_name( $table ) || !$this->admin->valid_deletable_table_name( $table )) {
			// 	$invalid_tables[] = $table;
			// }

			if ( !$this->admin->valid_deletable_table_name( $table )) {
				$invalid_tables[] = $table;
			}
		}
		if ( count( $invalid_tables ) > 0 ) {
			return new WP_REST_Response( [
				'success' => false,
				'data' => $tables,
				'message' => 'Cannot delete tables : ' . implode(',', $invalid_tables),
			], 400 );
		}
		try {
			$failed = [];
			foreach ( $tables as $table ) {
				$result = $this->admin->delete_table( $table );
				if ($result === false) {
					$failed[] = $table;
				} else {
					$this->core->log("✅ Deleted table '{$table}'");
				}
			}
			if ( count($failed) > 0 ) {
				return new WP_REST_Response( [
					'success' => false,
					'data' => $failed,
					'message' => 'Some tables could not be deleted : ' . implode(',', $failed) . ' (logged the detail in PHP Error Logs.)'
				], 200 );
			}
			return new WP_REST_Response( [
				'success' => true,
			], 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response([
				'success' => false,
				'data' => $tables,
				'message' => $e->getMessage(),
			], 500 );
		}
	}

	function rest_table( $request ) {
		$params = $request->get_json_params();
		$table = isset( $params['table'] ) ? $params['table'] : null;
		$offset = isset( $params['offset'] ) ? $params['offset'] : 0;
		if ( !$table ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Missing table parameters.' ], 400 );
		}
		if ( !$this->admin->valid_table_name( $table ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Invalid table name.' ], 400 );
		}
		$count = 0;
		if ( $offset === 0 ) {
			$count = $this->core->get_table_data_count( $table );
		}
		try {
			return new WP_REST_Response( [
				'success' => true,
				'data' => $this->core->get_table_data( $table, $offset ),
				'count' => $count,
			], 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response([
				'success' => false,
				'message' => $e->getMessage(),
			], 500 );
		}
	}

	function rest_optimize_tables( $request ) {
		$params = $request->get_json_params();
		$tables = isset( $params['tables'] ) ? $params['tables'] : null;
		if ( !$tables ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Missing tables parameters.' ], 400 );
		}
		$invalid_tables = [];
		foreach ( $tables as $table ) {
			if ( !$this->admin->valid_table_name( $table ) ) {
				$invalid_tables[] = $table;
			}
		}
		if ( count( $invalid_tables ) > 0 ) {
			return new WP_REST_Response( [
				'success' => false,
				'data' => $tables,
				'message' => 'Cannot optimize tables : ' . implode(',', $invalid_tables),
			], 400 );
		}
		try {
			$failed = [];
			foreach ( $tables as $table ) {
				$result = $this->admin->optimize_table( $table );
				if ($result === false) {
					$failed[] = $table;
				} else {
					$this->core->log("✅ Optimized table '{$table}'");
				}
			}
			if ( count($failed) > 0 ) {
				return new WP_REST_Response( [
					'success' => false,
					'data' => $failed,
					'message' => 'Some tables could not be optimized : ' . implode(',', $failed) . ' (logged the detail in PHP Error Logs.)'
				], 200 );
			}
			return new WP_REST_Response( [
				'success' => true,
			], 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response([
				'success' => false,
				'data' => $tables,
				'message' => $e->getMessage(),
			], 500 );
		}
	}

	function rest_repair_tables( $request ) {
		$params = $request->get_json_params();
		$tables = isset( $params['tables'] ) ? $params['tables'] : null;
		if ( !$tables ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Missing tables parameters.' ], 400 );
		}
		$invalid_tables = [];
		foreach ( $tables as $table ) {
			if ( !$this->admin->valid_table_name( $table ) ) {
				$invalid_tables[] = $table;
			}
		}
		if ( count( $invalid_tables ) > 0 ) {
			return new WP_REST_Response( [
				'success' => false,
				'data' => $tables,
				'message' => 'Cannot repair tables : ' . implode(',', $invalid_tables),
			], 400 );
		}
		try {
			$failed = [];
			foreach ( $tables as $table ) {
				$result = $this->admin->repair_table( $table );
				if ($result === false) {
					$failed[] = $table;
				} else {
					$this->core->log("✅ Repaired table '{$table}'");
				}
			}
			if ( count($failed) > 0 ) {
				return new WP_REST_Response( [
					'success' => false,
					'data' => $failed,
					'message' => 'Some tables could not be repaired : ' . implode(',', $failed) . ' (logged the detail in PHP Error Logs.)'
				], 200 );
			}
			return new WP_REST_Response( [
				'success' => true,
			], 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response([
				'success' => false,
				'data' => $tables,
				'message' => $e->getMessage(),
			], 500 );
		}
	}

	function rest_custom_query_count( $request ) {
		$item = sanitize_text_field( $request->get_param('item') );
		if ( !$item ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Missing item parameters.' ], 400 );
		}
		$query = "";
		$custom_queries = $this->core->get_option( 'custom_queries' );
		foreach ( $custom_queries as $custom_query ) {
			if ( $custom_query['item'] === $item ) {
				$query = $custom_query['query_count'];
				break;
			}
		}
		if ( !$query ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Not found the query for count.' ], 400 );
		}
		try {
			return new WP_REST_Response( [
				'success' => true,
				'data' => $this->core->do_custom_query_count( $query ),
			], 200 );
		} catch ( RuntimeException $e ) {
			return new WP_REST_Response( [
				'success' => false,
				'message' => $e->getMessage(),
			], 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response([
				'success' => false,
				'message' => $e->getMessage(),
			], 500 );
		}
	}

	function rest_custom_query_delete( $request ) {
		$params = $request->get_json_params();
		$item = isset( $params['item'] ) ? $params['item'] : null;
		$is_auto_clean = isset( $params['is_auto_clean'] ) ? $params['is_auto_clean'] : false;
		if ( !$item ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Missing item parameters.' ], 400 );
		}
		$query = "";
		$clean_style = "";
		$name = "";
		$custom_queries = $this->core->get_option( 'custom_queries' );
		foreach ( $custom_queries as $custom_query ) {
			if ( $custom_query['item'] === $item ) {
				$query = $custom_query['query_delete'];
				$clean_style = $custom_query['clean_style'];
				$name = $custom_query['name'];
				break;
			}
		}
		if ( !$this->admin->valid_custom_query_operation( $clean_style, $is_auto_clean ) ) {
			return new WP_REST_Response( [
				'success' => false,
				'message' => 'Cannot call this custom query due to its clean style.'
			], 400 );
		}
		if ( !$query ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Not found the query for deleting.' ], 400 );
		}
		try {
			$result = $this->core->do_custom_query_delete( $query );
			if ($is_auto_clean) {
				$this->core->log("✅ {$name}: deleted {$result} entries.");
			}
			return new WP_REST_Response( [
				'success' => true,
				'data' => [
					'deleted' => $result,
				],
			], 200 );
		} catch ( RuntimeException $e ) {
			return new WP_REST_Response( [
				'success' => false,
				'message' => $e->getMessage(),
			], 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response([
				'success' => false,
				'message' => $e->getMessage(),
			], 500 );
		}
	}

	function rest_refresh_logs() {
		return new WP_REST_Response( [ 'success' => true, 'data' => $this->core->get_logs() ], 200 );
	}

	function rest_clear_logs() {
		$this->core->clear_logs();
		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	function rest_generate_fake_data() {
		$age_threshold = $this->core->get_option( 'aga_threshold' );
		$age_threshold = $age_threshold === 'none' ? 0 : $age_threshold;

		// WordPress Core
		$queries = new Meow_DBCLNR_Queries();
		foreach ( $queries::$GENERATE_FAKE_DATA as $item => $value ) {
			$queries->query_generate_fake_data( $item, $age_threshold );
		}

		// Post Type
		$queries->generate_fake_post_type( $age_threshold );

		// Table
		$this->core->generate_fake_table();

		// Options
		update_option( 'dbclnr_fake_option', 'dbclnr_fake_option_value', '', false );

		// Cron Jobs
		$this->core->generate_fake_cron_job();

		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	protected function get_installed_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins = get_plugins();

		if ( empty( $plugins ) ) return [];

		$installed_plugins = [];
		foreach ( $plugins as $path => $data ) {
			$dir = explode( '/', $path );
			$installed_plugins[] = [
				'name' => $data['Name'],
				'slug' => $dir[0],
			];
		}
		return $installed_plugins;
	}

	protected function get_auto_clean_items() {
		$list_post_types = $this->core->get_post_types();
		$list = [];
		foreach ( $list_post_types as $post_type ) {
			$list[] = [
				'item' => 'list_post_types_' . $post_type,
				'name' => $post_type
			];
		}
		$all_items = [
			Meow_DBCLNR_Items::$POSTS,
			Meow_DBCLNR_Items::$POSTS_METADATA,
			Meow_DBCLNR_Items::$USERS,
			Meow_DBCLNR_Items::$COMMENTS,
			Meow_DBCLNR_Items::$TRANSIENTS,
			$list,
		];
		$items = [];
		foreach ( $all_items as $item ) {
			$items = array_merge( $items, $this->core->add_clean_style_data( $item ) );
		}

		$auto_clean_items = [];
		$bulk_batch_size = $this->core->get_option( 'bulk_batch_size' );
		foreach ( $items as $item ) {
			if ( $item['clean_style'] === 'auto' ) {
				$count = $this->get_entry_count( $item['item'] );
				$auto_clean_items[] = [
					'item' => $item['item'],
					'name' => $item['name'],
					'count' => (int)$count,
					'times' => ceil( $count / $bulk_batch_size ),
				];
			}
		}

		return $auto_clean_items;
	}

	protected function get_entry_count( $item ) {
		$age_threshold = $this->core->get_option( 'aga_threshold' );
		$age_threshold = $age_threshold === 'none' ? 0 : $age_threshold;

		if ( array_key_exists( $item, Meow_DBCLNR_Queries::$COUNT ) ) {
			$queries = new Meow_DBCLNR_Queries();
			return $queries->query_count( $item, $age_threshold );
		}

		$post_type = null;
		$post_status = null;
		$param = $this->get_item_param( $item );
		if ( !$param ) {
			return false;
		}
		if ( $param['var'] === 'post_type' ) {
			$post_type = $param['value'];
		}
		return (int)$this->core->get_entry_count( $post_type, $post_status, $age_threshold );
	}

	protected function get_item_param( $item ) {
		$param = null;
		if ( strpos($item, 'list_post_types_') === 0 ) {
			$param = [
				'var' => 'post_type',
				'value' => str_replace( [ 'list_post_types_' ], '', $item )
			];
		}
		return $param;
	}

	protected function add_table_info_data ( $list ) {
		$data = array();
		foreach ( $list as $item ) {
			$data[] = array_merge( $item, [
				'info' => apply_filters( 'dbclnr_check_table_info', $item['table'], null )
			] );
		}
		return $data;
	}

	protected function add_post_type_info_data( $list ) {
		$data = array();
		foreach ( $list as $item ) {
			$data[] = array_merge( $item, [
				'info' => apply_filters( 'dbclnr_check_post_type_info', $item['name'], null )
			] );
		}
		return $data;
	}

	protected function add_option_info( $list ) {
		$data = array();
		foreach ( $list as $item ) {
			$data[] = array_merge( $item, [
				'info' => apply_filters( 'dbclnr_check_option_info', $item['option_name'], null )
			] );
		}
		return $data;
	}

	protected function add_cron_info( $list ) {
		$data = array();
		foreach ( $list as $jobs ) {
			$data[] = array_merge( $jobs, [
				'info' => apply_filters( 'dbclnr_check_cron_info', $jobs['cron_name'], null ),
			] );
		}
		return $data;
	}

	protected function is_finished( $affected ) {
		$limit = $this->core->get_option( 'bulk_batch_size' );
		return $affected < $limit;
	}
}
