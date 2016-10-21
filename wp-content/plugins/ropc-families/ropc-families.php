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

		if ( isset( $_POST[ 'update_field' ], $_POST[ 'id' ] ) && is_string( $_POST[ 'update_field' ] ) && is_scalar( $_POST[ 'id' ] ) )
		{
			$field = explode( '.', $_POST[ 'update_field' ] );
			if ( count( $field ) != 2 || !in_array( $field[ 0 ], [ 'ropc_family', 'ropc_family_member' ] )
				|| $field[ 1 ] == 'id' )
			{
				header( 'HTTP/1.1 400 Bad request' );
				echo "Invalid field '$_POST[update_field]'";
				exit;
			}

			try
			{
				global $wpdb;
				$wpdb->update( $field[ 0 ], [ $field[ 1 ] => ( trim( $_POST[ 'value' ] ) ?: NULL ) ], [ 'id' => $_POST[ 'id' ] ] );
			}
			catch ( Exception $e )
			{
				header( 'HTTP/1.1 500 Internal server error' );
				echo "problem";//$e->message();
				exit;
			}

			echo "Updated successfully";
			exit;
		}

		header( 'HTTP/1.1 400 Bad request' );
		echo "Incomplete request";
		exit;
	}

} // end class

new RopcFamilies();

?>
