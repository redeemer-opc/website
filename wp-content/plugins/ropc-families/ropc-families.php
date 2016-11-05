<?php
/*
Plugin Name: Redeemer OPC Families
Description: Ajax functionality for editing family info
Author: Will Groenendyk
*/

class RopcFamilies
{
	/**
	 * Determines if the current user can edit the families info
	 */
	public static function can_edit()
	{
		$user = wp_get_current_user();
		return (bool) array_intersect( [ 'editor', 'administrator' ], (array) $user->roles );
	}
	
	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct()
	{
		// Include the Ajax library on the front end
		add_action( 'wp_head', array( &$this, 'add_ajax_library' ) );

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( &$this, 'register_plugin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'register_plugin_scripts' ) );

		add_action( 'wp_ajax_update_ropc_member_info', array( &$this, 'update_ropc_member_info' ) );
		add_action( 'wp_ajax_update_ropc_picture', array( &$this, 'update_ropc_picture' ) );

	} // end constructor

	/**
	 * Registers and enqueues plugin-specific styles.
	 */
	public function register_plugin_styles()
	{
		if ( static::can_edit() )
		{
			wp_register_style( 'ropc_families_edit', plugins_url( 'ropc-families/css/edit.css' ) );
			wp_enqueue_script( 'jquery-form', [ 'jquery' ], FALSE, TRUE ); 
			wp_enqueue_style( 'ropc_families_edit' );
		}

	} // end register_plugin_styles

	/**
	 * Registers and enqueues plugin-specific scripts.
	 */
	public function register_plugin_scripts()
	{
		if ( static::can_edit() )
		{
			wp_register_script( 'fontawesome', 'https://use.fontawesome.com/b2e4717b55.js' );
			wp_enqueue_script( 'fontawesome' );
			wp_register_script( 'inputmask', plugins_url( 'ropc-families/js/jquery.inputmask.bundle.min.js' ) );
			wp_enqueue_script( 'inputmask' );
			wp_register_script( 'ropc-families-edit', plugins_url( 'ropc-families/js/plugin.js' ), array( 'jquery' ) );
			wp_enqueue_script( 'ropc-families-edit' );
		}

	} // end register_plugin_scripts

	/**
	 * Adds the WordPress Ajax Library to the frontend.
	 */
	public function add_ajax_library()
	{
		$html =  '<script type="text/javascript">';
		$html .= 'var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '"';
		$html .= '</script>';

		echo $html;
	} // end add_ajax_library

	public function update_ropc_member_info()
	{
		if ( !static::can_edit() )
		{
			header( 'HTTP/1.1 403 Forbidden' );
			echo "You are not authorized to perform this action";
			exit;
		}

		$response_data = [];
		
		if ( !isset( $_POST[ 'data_action' ] ) )
		{
			header( 'HTTP/1.1 400 Bad request' );
			echo "No 'data_action' parameter given";
			exit;
		}
		$data_action = $_POST[ 'data_action' ];

		if ( !isset( $_POST[ 'table' ] ) || !in_array( $_POST[ 'table' ], [ 'ropc_family', 'ropc_family_member' ] ) )
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
			if ( !isset( $_POST[ 'fields' ] ) || !is_array( $_POST[ 'fields' ] ) )
			{
				header( 'HTTP/1.1 400 Bad request' );
				echo "'fields' parameter is invalid or missing";
				exit;
			}
			$fields = array_map( function( $val )
			{
				return trim( $val ) ?: NULL;
			}, $_POST[ 'fields' ] );
			unset( $fields[ 'id' ] );
			
			foreach ( [ 'birthday', 'anniversary' ] as $date_field )
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

		if ( $data_action == 'update' )
		{
			try
			{
				global $wpdb;
				$wpdb->update( $table, $fields, [ 'id' => $id ] );
			}
			catch ( Exception $e )
			{
				header( 'HTTP/1.1 500 Internal server error' );
				echo $e->getMessage();
				exit;
			}

			wp_send_json( $response_data + $fields );
		}
		else if ( $data_action == 'create' )
		{
			try
			{
				global $wpdb;
				$wpdb->insert( $table, $fields );
			}
			catch ( Exception $e )
			{
				header( 'HTTP/1.1 500 Internal server error' );
				echo "problem";//$e->message();
				exit;
			}

			$fields[ 'id' ] = $wpdb->insert_id;
			wp_send_json( $response_data + $fields );
		}
		else if ( $data_action == 'delete' )
		{
			try
			{
				global $wpdb;
				$wpdb->delete( $table, [ 'id' => $id] );
			}
			catch ( Exception $e )
			{
				header( 'HTTP/1.1 500 Internal server error' );
				echo "problem";//$e->message();
				exit;
			}

			wp_send_json( $response_data + [ 'success' => TRUE ] );
		}
		header( 'HTTP/1.1 400 Bad request' );
		echo "Invalid action '$data_action'";
		exit;
	}
	
	public function update_ropc_picture()
	{
		$family_id = $_POST[ 'family_id' ];
		global $wpdb;

		//require the needed files
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
		//then loop over the files that were sent and store them using  media_handle_upload();
		if ($_FILES) {
			foreach ($_FILES as $file => $array) {
				if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
					echo "upload error : " . $_FILES[$file]['error'];
					die();
				}
				$attach_id = media_handle_upload( $file, $post_id );
				
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

?>
