<?php
/*
Plugin Name: Redeemer OPC Families
Description: Ajax functionality for editing family info
Author: Will Groenendyk
*/

class RopcFamilies
{
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

	} // end constructor

	/**
	 * Registers and enqueues plugin-specific styles.
	 */
	public function register_plugin_styles()
	{
		wp_register_style( 'ropc_families', plugins_url( 'ropc-families/css/plugin.css' ) );
		wp_enqueue_style( 'ropc_families' );

	} // end register_plugin_styles

	/**
	 * Registers and enqueues plugin-specific scripts.
	 */
	public function register_plugin_scripts()
	{
		wp_register_script( 'ropc-families', plugins_url( 'ropc-families/js/plugin.js' ), array( 'jquery' ) );
		wp_enqueue_script( 'ropc-families' );

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
		$user = wp_get_current_user();
		if ( ! array_intersect( [ 'editor', 'administrator' ], (array) $user->roles ) )
		{
			header( 'HTTP/1.1 403 Forbidden' );
			echo "You are not authorized to perform this action";
			exit;
		}

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

		if ( $data_action == 'update' )
		{
			if ( !isset( $_POST[ 'id' ] ) )
			{
				header( 'HTTP/1.1 400 Bad request' );
				echo "No 'id' parameter given";
				exit;
			}
			$id = $_POST[ 'id' ];

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

			echo "Updated successfully";
			exit;
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

			wp_send_json( [ 'id' => $wpdb->insert_id ] );
		}

		header( 'HTTP/1.1 400 Bad request' );
		echo "Invalid action '$data_action'";
		exit;
	}

} // end class

new RopcFamilies();

?>
