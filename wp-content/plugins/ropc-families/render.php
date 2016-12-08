<?php

/**
 * @file
 *	Methods for rendering different elements of the member center; most methods here perform some
 *	sort of processing on some data and then inject the data into an HTML template
 */

/**
 * @brief
 *	Injects the given data into a template and renders the template
 *
 * @param string $template_name
 *	The name of a .tpl.php file located under this plugin's "templates" directory (exclude the
 *	leading "templates/" and ".tpl.php")
 * @param array  $vars
 *	OPTIONAL. An array to be extracted into scope, available for the template to user_error
 *
 * @retval string
 *	The HTML of the rendere template
 */
function ropc_render( $template_name, array $vars = [] )
{
	extract( $vars );
	ob_start();
	include( 'templates/' . $template_name . '.tpl.php' );
	return ob_get_clean();
}

/**
 * @brief
 *	Renders a person
 *
 * @param array $person (can be an empty array)
 * @param bool  $can_edit
 *
 * @retval string
 */
function ropc_families_display_person( array $person, $can_edit )
{
	$vars = $person + [
		'first_name' => '',
		'last_name' => '',
		'maiden_name' => '',
		'cellphone' => '',
		'occupation' => '',
		'email' => '',
		'birthday' => '',
		'mem_id' => '',
		'family_role' => '',
		'type' => '',
		'can_edit' => $can_edit,
	];

	$vars[ 'type_options' ] = [
		''                    => '',
		'member'              => 'Member',
		'noncommuning_member' => 'Noncommuning Member',
		'regular_attendee'    => 'Regular Attendee',
	];
	
	$vars[ 'is_parent' ] = in_array( $vars[ 'family_role' ], [ '', 'husband', 'wife' ] );
	
	$vars[ 'birthday' ] = $vars[ 'birthday' ]
		? date( 'F j', strtotime( $vars[ 'birthday' ] ) )
		: '';
	
	return ropc_render( 'person', $vars );
}

/**
 * @brief
 *	Renders a page of the member center
 *
 * @param string $type
 * @param array  $data
 *
 * @retval string
 */
function ropc_families_display_member_center_page( $type = 'families', array $data = [] )
{
	$vars = [
		'type' => $type,
		'data' => $data,
	];
	return ropc_render( 'member_center_page', $vars );
}

/**
 * @brief
 *	Renders the navbar for the member center
 *
 * @param data  $data
 * @param array $divisions
 *	Indicates how to group the families by last name
 *
 * @retval string
 */
function ropc_families_display_navbar( array $data,	array $divisions = [ 'A', 'F', 'K', 'P' ] )
{
	$vars = [
		'data' => $data,
	];

	$current_page = $data[ 'current_page' ];
	$search_terms = $data[ 'search_terms' ];
	$vars[ 'divisions_processed' ] = [];

	// Iterates through $divisions in order to determine the last last letter in the division as
	// well as which division is active (if any)
	for ( $i = 0; $i < count( $divisions ); $i ++ )
	{
		$start = $divisions[ $i ];

		$end = $i + 1 < count( $divisions )
			? $divisions[ $i + 1 ]
			: 'Z';
		if ( $end != 'Z' )
		{
			$end = chr( ord( $end ) - 1 );
		}

		$key = "$start-$end";
		$vars[ 'divisions_processed' ][ $key ] = [
			'text'  => "$start - $end",
			'class' => strtolower( $current_page ) == strtolower( $key )
				? 'active'
				: '',
		];
	}

	// Because we'll use the search terms as an HTML attribute, escape any potentially troublesome
	// characters
	$vars[ 'search_safe' ] = $search_terms
		? htmlspecialchars( $search_terms )
		: '';
	
	return ropc_render( 'member_center_navbar', $vars );
}

/**
 * @brief
 *	Renders an entire family
 *
 * @param array $family (can be an empty array)
 * @param bool  $can_edit
 *
 * @retval string
 *	The HTML to display the family
 */
function ropc_families_display_family( array $family, $can_edit )
{
	$vars = $family + [
		'fam_id' => '',
		'address1' => '',
		'address2' => '',
		'city' => '',
		'state' => '',
		'home_phone' => '',
		'anniversary' => '',
		'parents' => [
			[ 'family_role' => 'husband' ],
			[ 'family_role' => 'wife' ],
		],
		'children' => [],
		'zip' => '',
		'name_fl' => 'New Family',
		'name_lf' => 'New Family',
		'picture_id' => 0,
		'picture_caption' => '',
		'husband' => [],
		'wife' => [],
		'can_edit' => $can_edit,
	];

	foreach ( $vars[ 'parents' ] as $parent )
	{
		if ( $parent[ 'family_role' ] == 'husband' )
		{
			$vars[ 'husband' ] = $parent;
		}
		else
		{
			$vars[ 'wife' ] = $parent;
		}
	}
	
	$vars[ 'husband' ] = $can_edit || $vars[ 'husband' ]
		? ropc_families_display_person( $vars[ 'husband' ], $can_edit )
		: '';
	$vars[ 'wife' ] = $can_edit || $vars[ 'wife' ]
		? ropc_families_display_person( $vars[ 'wife' ] + [ 'family_role' => 'wife' ], $can_edit )
		: '';

	foreach ( $vars[ 'children' ] as &$child )
	{
		$child = ropc_families_display_person( $child, $can_edit );
	}
	$vars[ 'child_template' ] = $can_edit
		? ropc_families_display_person( [ 'family_role' => 'child' ], TRUE )
		: '';
	
	$vars[ 'anniversary' ] = $vars[ 'anniversary' ]
		? date( 'F j', strtotime( $vars[ 'anniversary' ] ) )
		: '';
	
	$vars[ 'pic_info_full' ]  = wp_get_attachment_image_src( $vars[ 'picture_id' ], 'large' );
	$vars[ 'pic_src_full' ]   = is_array( $vars[ 'pic_info_full' ] ) ? $vars[ 'pic_info_full' ][ 0 ] : '';
	$vars[ 'pic_info_thumb' ] = wp_get_attachment_image_src( $vars[ 'picture_id' ] );
	$vars[ 'pic_src_thumb' ]  = is_array( $vars[ 'pic_info_thumb' ] ) ? $vars[ 'pic_info_thumb' ][ 0 ] : '';

	$vars[ 'show_contact_info' ] = $can_edit || $vars[ 'address1' ] || $vars[ 'address2' ]
		|| $vars[ 'city' ] || $vars[ 'state' ] || $vars[ 'zip' ] || $vars[ 'home_phone' ]
		|| $vars[ 'anniversary' ];
		
	return ropc_render( 'family', $vars );
}