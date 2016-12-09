<?php
/*
Plugin Name: Redeemer OPC Families
Description: Ajax functionality for editing family info
Author: Will Groenendyk
*/

require_once( 'render.php' );
require_once( 'data.php' );

class RopcFamilies
{
	/**
	 * @brief
	 *	Determines if the current user can edit the families info
	 */
	public static function can_edit()
	{
		$user = wp_get_current_user();
		return (bool) array_intersect( [ 'editor', 'administrator' ], (array) $user->roles );
	}
	
	/**
	 * @brief
	 *	Determines if the current user can view the member center
	 */
	public static function can_view()
	{
		$user = wp_get_current_user();
		return (bool) static::can_edit() || array_intersect( [ 'subscriber' ], (array) $user->roles );
	}
	
	/**
	 * @brief
	 *	Determines if the current user should see the edit interface
	 */	
	public static function in_edit_mode()
	{
		return static::can_edit() && empty( $_GET[ 'preview' ] );
	}
	
	/**
	 * @brief
	 *	Processes a request for pages in the member center and handles the request appropriately
	 */
	public static function display_member_center_page()
	{
		if ( ! static::can_view() )
		{
			echo "You are not authorized to view this page";
			return;
		}

		// Validate GET params
		$bd_month = isset( $_GET[ 'bd-month' ] ) && preg_match( '/^(1[210]|\d)$/', $_GET[ 'bd-month' ] )
			? $_GET[ 'bd-month' ]
			: FALSE;
		$anniv_month = isset( $_GET[ 'anniv-month' ] ) && preg_match( '/^(1[210]|\d)$/', $_GET[ 'anniv-month' ] )
			? $_GET[ 'anniv-month' ]
			: FALSE;
		$current_page = isset( $_GET[ 'page' ] )
			? $_GET[ 'page' ]
			: '';
		$search_terms = isset( $_GET[ 'search' ] )
			? $_GET[ 'search' ]
			: '';

		/* Determine the type of page to show:
		 *	- If no valid GET params were given, show the "splash" page
		 *	- If one or more valid GET params were given, use the following order of precence
		 *		1. Families page (e.g., families A-C)
		 *		2. Families search
		 *		3. Anniversaries
		 *		4. Birthdays
		 */
		$type = $current_page || $search_terms ? 'families' : 'splash';
		if ( $bd_month )
		{
			$type = 'birthday';
		}
		elseif ( $anniv_month )
		{
			$type = 'anniversary';
		}

		// Generate a map where keys are month numbers and point to the respective month's name
		// (1 => 'January')
		$months = [];
		for ( $i = 1; $i <= 12; $i++ )
		{
			$date_obj = DateTime::createFromFormat( '!m', $i );
			$months[ $i ] = $date_obj->format( 'F' );
		}
		
		$data = [
			'alternate_view_url' => FALSE,
			'in_edit_mode'       => static::in_edit_mode(),
			'search_terms'       => $search_terms,
			'current_page'       => $current_page,
			'months'             => $months,
			'bd_month_n'         => FALSE,
			'bd_month'           => FALSE,
			'anniv_month_n'      => FALSE,
			'anniv_month'        => FALSE,
		];
		
		$condition = '';
		$db_params = [];
		$alternate_view_url = FALSE;
		if ( $type == 'families' )
		{
			if ( $search_terms )
			{
				$condition = 'last_name LIKE %s';
				$db_params = [ "$search_terms%" ];
			}
			elseif ( $current_page && preg_match( '/^[a-z]-[a-z]$/i', $current_page ) )
			{
				$condition = "last_name REGEXP %s";
				$db_params = [ "^[$current_page]" ];
			}
			
			if ( static::can_edit() )
			{
				$get_params = array_intersect_key( $_GET, [ 'search' => '', 'page' => '' ] );
				if ( $data[ 'in_edit_mode' ] )
				{
					$get_params[ 'preview' ] = TRUE;
				}
				$data[ 'alternate_view_url' ] = '/member-center/?' . http_build_query( $get_params );
			}
			
			$data[ 'families' ] = ropc_get_families( $condition, $db_params );
		}
		elseif ( $type == 'birthday' )
		{
			$data[ 'bd_month_n' ] = $bd_month;
			$data[ 'bd_month' ] = $months[ $bd_month ];
			$data[ 'by_day' ] = ropc_get_birthdays( $bd_month );
		}
		elseif ( $type == 'anniversary' )
		{
			$data[ 'anniv_month_n' ] = $anniv_month;
			$data[ 'anniv_month' ] = $months[ $anniv_month ];
			$data[ 'by_day' ] = ropc_get_anniversaries( $anniv_month );
		}
		
		return ropc_families_display_member_center_page( $type, $data );
	}

	/**
	 * @brief
	 *	Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct()
	{
		// Include the Ajax library on the front end
		add_action( 'wp_head', array( &$this, 'add_ajax_library' ) );

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts',  array( &$this, 'register_plugin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'register_plugin_scripts' ) );

		add_action( 'wp_ajax_update_ropc_member_info', array( &$this, 'update_ropc_member_info' ) );
		add_action( 'wp_ajax_update_ropc_picture', array( &$this, 'update_ropc_picture' ) );

	} // end constructor

	/**
	 * @brief
	 *	Registers and enqueues plugin-specific styles.
	 */
	public function register_plugin_styles()
	{
		if ( static::in_edit_mode() )
		{
			wp_register_style( 'ropc_families_edit', plugins_url( 'ropc-families/less/edit.css.less' ) );
			wp_enqueue_style( 'ropc_families_edit' );
		}
		
		if ( static::can_view() )
		{
			wp_register_style( 'ropc_families_view', plugins_url( 'ropc-families/less/view.css.less' ) );
			wp_enqueue_style( 'ropc_families_view' );			
		}

	} // end register_plugin_styles

	/**
	 * @brief
	 *	Registers and enqueues plugin-specific scripts.
	 */
	public function register_plugin_scripts()
	{
		if ( static::in_edit_mode() )
		{
			wp_register_script( 'inputmask', plugins_url( 'ropc-families/js/jquery.inputmask.bundle.min.js' ) );
			wp_enqueue_script( 'inputmask' );
			
			wp_register_script( 'ropc_families_edit', plugins_url( 'ropc-families/js/edit.js' ), array( 'jquery' ) );
			wp_enqueue_script( 'ropc_families_edit' );
			
			wp_enqueue_script( 'jquery-form', [ 'jquery' ], FALSE, TRUE ); 
		}
		
		if ( static::can_view() )
		{
			wp_register_script( 'fontawesome', 'https://use.fontawesome.com/b2e4717b55.js' );
			wp_enqueue_script( 'fontawesome' );
				
			wp_register_script( 'ropc_families_view', plugins_url( 'ropc-families/js/view.js' ), array( 'jquery' ) );
			wp_enqueue_script( 'ropc_families_view' );
		}

	} // end register_plugin_scripts

	/**
	 * @brief
	 *	Adds the WordPress Ajax Library to the frontend.
	 */
	public function add_ajax_library()
	{
		$html =  '<script type="text/javascript">';
		$html .= 'var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '";';
		$html .= 'var can_edit = ' . ( static::in_edit_mode() ? 'true' : 'false' ) . ';';
		$html .= '</script>';

		echo $html;
	} // end add_ajax_library

	/**
	 * @brief 
	 *	Handles ajax requests for updating member data.
	 *
	 * @note
	 *	Requests MUST be POST requests
	 */
	public function update_ropc_member_info()
	{
		if ( !static::can_edit() )
		{
			header( 'HTTP/1.1 403 Forbidden' );
			echo "You are not authorized to perform this action";
			exit;
		}

		$response_data = [];
		$valid_data_actions = [ 'create', 'update', 'delete' ];
		$valid_table_names = [ 'ropc_family', 'ropc_family_member' ];
		$valid_member_types = [ 'member', 'noncommuning_member', 'regular_attendee' ];
		$date_fields = [ 'birthday', 'anniversary' ];
		
		// Validate	'data_action'
		if ( !isset( $_POST[ 'data_action' ] )
			|| !in_array( $_POST[ 'data_action' ], $valid_data_actions )  )
		{
			header( 'HTTP/1.1 400 Bad request' );
			echo "Missing or invalid 'data_action' parameter";
			exit;
		}
		$data_action = $_POST[ 'data_action' ];

		// Validate the given table
		if ( !isset( $_POST[ 'table' ] )
			|| !in_array( $_POST[ 'table' ], $valid_table_names ) )
		{
			header( 'HTTP/1.1 400 Bad request' );
			echo "'table' parameter invalid or missing";
			exit;
		}
		$table = $_POST[ 'table' ];

		$fields = [];
		$id = '';
		if ( in_array( $data_action, [ 'update', 'create' ] ) )
		{
			// Validate fields to populate/update
			if ( !isset( $_POST[ 'fields' ] ) || !is_array( $_POST[ 'fields' ] ) )
			{
				header( 'HTTP/1.1 400 Bad request' );
				echo "'fields' parameter is invalid or missing";
				exit;
			}
			// Perform basic processing on each field's value (trim strings, convert '' => NULL)
			$fields = array_map( function( $val )
			{
				return trim( $val ) ?: NULL;
			}, $_POST[ 'fields' ] );
			// Disallow setting/updating any `id` field
			unset( $fields[ 'id' ] );
			
			// Validate `type` field
			if ( isset( $fields[ 'type' ] ) )
			{
				if ( ! in_array( $fields[ 'type' ], $valid_member_types ) )
				{
					$fields[ 'type' ] = NULL;
				}
			}
			
			// Validate date fields
			foreach ( $date_fields as $date_field )
			{
				if ( isset( $fields[ $date_field ] ) )
				{
					$unix_timestamp = strtotime( $fields[ $date_field ] );
					$fields[ $date_field ] = $unix_timestamp
						? date( "Y-m-d H:i:s", $unix_timestamp )
						: NULL;
					$response_data[ $date_field ] = $unix_timestamp
						? date( "F j", $unix_timestamp )
						: NULL;
				}
			}

		}
		
		// If we are updating or deleting records, we need a usable id; validate the id provided by
		// the client
		if ( in_array( $data_action, [ 'delete', 'update' ] ) )
		{
			if ( !isset( $_POST[ 'id' ] ) || !is_scalar( $_POST[ 'id' ] ) )
			{
				header( 'HTTP/1.1 400 Bad request' );
				echo "'id' parameter is invalid or missing";
				exit;
			}
			$id = $_POST[ 'id' ];		
		}

		// Perform the requested DB change
		global $wpdb;
		try
		{
			if ( $data_action == 'create' )
			{
				$wpdb->insert( $table, $fields );
				$fields[ 'id' ] = $wpdb->insert_id;
				wp_send_json( $response_data + $fields );
			}
			else if ( $data_action == 'update' )
			{
				$wpdb->update( $table, $fields, [ 'id' => $id ] );
				wp_send_json( $response_data + $fields );
			}
			else if ( $data_action == 'delete' )
			{

				$wpdb->delete( $table, [ 'id' => $id] );
				wp_send_json( $response_data + [ 'success' => TRUE ] );
			}
		}
		catch ( Exception $e )
		{
			header( 'HTTP/1.1 500 Internal server error' );
			echo $e->getMessage();
			exit;
		}

		// In theory, we should never get here
		header( 'HTTP/1.1 400 Bad request' );
		echo "Invalid action '$data_action'";
		exit;
	}
	
	/**
	 * @brief
	 *	Handles the upload of family pictures
	 */
	public function update_ropc_picture()
	{
		$family_id = $_POST[ 'family_id' ];
		global $wpdb;

		// Require the needed files
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
		
		// Loop over the files that were sent and store them using media_handle_upload();
		if ( $_FILES )
		{
			foreach ( $_FILES as $file => $info )
			{
				if ( $info[ 'error' ] !== UPLOAD_ERR_OK )
				{
					echo "upload error : " . $info[ 'error' ];
					die();
				}
				$attach_id = media_handle_upload( $file, NULL );
				$wpdb->update( 'ropc_family', [ 'picture_id' => $attach_id  ], [ 'id' => $family_id ] );
			}   
		}
		
		wp_send_json( [
			'thumb' => wp_get_attachment_image_src( $attach_id ),
			'full' => wp_get_attachment_image_src( $attach_id, 'large' ),			
		] );
	}

} // end class

new RopcFamilies();
