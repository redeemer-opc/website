<?php

function theme_enqueue_styles() {

    $parent_style = 'parent-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style )
    );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function ropc_get_families()
{
	static $family_wide_attrs = [
		'address1',
		'address2',
		'city',
		'state',
		'zip',
		'anniversary',
		'home_phone',
		'fam_id',
		'picture_id',
	];
	static $person_attrs = [
		'first_name',
		'middle_name',
		'last_name',
		'birthday',
		'occupation',
		'family_role',
		'mem_id',
		'maiden_name',
		'type',
		'cellphone',
		'email'
	];

	global $wpdb;
	$families_raw = $wpdb->get_results( '
		SELECT ropc_family.id AS fam_id,
			ropc_family_member.id AS mem_id,
			address1,
			address2,
			city,
			state,
			zip,
			anniversary,
			home_phone,
			email,
			family_role,
			first_name,
			middle_name,
			last_name,
			birthday,
			occupation,
			cellphone,
			type,
			maiden_name,
			picture_id
		FROM ropc_family
			INNER JOIN ropc_family_member ON ropc_family.id = family_id'
	);

	$families_processed = [];
	foreach ( $families_raw as $family )
	{
		$family_id = $family->fam_id;
		if ( ! isset( $families_processed[ $family_id ] ) )
		{
			$families_processed[ $family_id ] = [
				'parents' => [],
				'children' => [],
			];
			foreach ( $family_wide_attrs as $attr )
			{
				$families_processed[ $family_id ][ $attr ] = $family->$attr;
			}
		}

		$person = [];
		foreach ( $person_attrs as $attr )
		{
			$person[ $attr ] = $family->$attr;
		}

		if ( $family->family_role == 'husband' || $family->family_role == 'wife' )
		{
			$families_processed[ $family_id ][ 'parents' ][] = $person;
		}
		else
		{
			$families_processed[ $family_id ][ 'children' ][] = $person;
		}
	}

	foreach ( $families_processed as &$family )
	{
		$family_name_lf;
		$family_name_fl;
		$male_ln = '';
		$male_fn = '';
		$female_ln = '';
		$female_fn = '';
		foreach ( $family[ 'parents' ] as $parent )
		{
			if ( $parent[ 'family_role' ] == 'husband' )
			{
				$male_ln = $parent[ 'last_name' ];
				$male_fn = $parent[ 'first_name' ];
			}
			else
			{
				$female_ln = $parent[ 'last_name' ];
				$female_fn = $parent[ 'first_name' ];
			}
		}

		if ( $male_fn && $female_fn )
		{
			$family_name_lf = "$male_ln, $male_fn and $female_fn";

			if ( $female_ln != $male_ln )
			{
				$family_name_lf .= " $female_ln";
				$family_name_fl = "$male_fn $male_ln and $female_fn $female_ln";
			}
			else
			{
				$family_name_fl = "$male_fn and $female_fn $male_ln";
			}
		}
		else
		{
			$first = '';
			$last = '';
			if ( $male_fn )
			{
				$first = $male_fn;
				$last = $male_ln;
			}
			else
			{
				$first = $female_fn;
				$last = $female_ln;
			}
			$family_name_lf = "$last, $first";
			$family_name_fl = "$first $last";
		}
		$family[ 'name_fl' ] = $family_name_fl;
		$family[ 'name_lf' ] = $family_name_lf;
	}

	return $families_processed;
}
