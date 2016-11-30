<?php

/**
 * @file
 *	Methods for retrieving data on members and families from the database
 */

/**
 * @brief
 *	Retrieves a set of families, optionally filtered by a condition
 *
 * @param string $condition
 *	OPTIONAL. A condition to include in the WHERE clause of the query; matches against both the
 *	ropc_family and ropc_family_member tables. Note that just one member from a family needs to meet
 *	the given condition in order for the entire family to be included
 * @param array $db_params
 *	OPTIONAL. If $condition is given and includes parameter placeholders, this populates those
 *	placeholders
 *
 * @retval array
 *	An indexed array of arrays, which each inner array in the format:
 *	@code
 *	array(
 *		'address1'        => '1234',
 *		'address2'        => '',
 *		'city'            => 'Ada',
 *		'state'           => 'MI',
 *		'zip'             => '12345',
 *		'anniversary'     => '2016-11-11',
 *		'home_phone'      => '(123) 456-7890',
 *		'fam_id'          => 1,
 *		'picture_id'      => 1,
 *		'picture_caption' => 'Lorem Ipsum',
 *		'anniversary_day' => '23',
 *		'parents'         => [],
 *		'children'        => [],
 *		'name_lf'         => 'Smith, John and Amy',
 *		'name_fl'         => 'John and Amy Smith',
 *	)
 *	@endcode
 */
function ropc_get_families( $condition = '', array $db_params = [] )
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
		'picture_caption',
		'anniversary_day',
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
	
	$family_ids = NULL;
	if ( $condition )
	{
		$query = "
			SELECT DISTINCT ropc_family.id
			FROM ropc_family
				INNER JOIN ropc_family_member ON ropc_family.id = family_id
			WHERE $condition";
		array_unshift( $db_params, $query );
	 	$family_ids = $wpdb->get_results( call_user_func_array( [ $wpdb, 'prepare' ], $db_params ) );
	}

	$where = '';
	if ( $family_ids !== NULL )
	{
		$ids = [];
		foreach ( $family_ids as $record )
		{
			$ids[] = $record->id;
		}
		$where = $ids
			? 'WHERE family_id IN (' . implode( ',', $ids ) . ')'
			: 'WHERE family_id <> family_id';
	}

	$families_raw = $wpdb->get_results( '
		SELECT ropc_family.id AS fam_id,
			ropc_family_member.id AS mem_id,
			address1,
			address2,
			city,
			state,
			zip,
			anniversary,
			day( anniversary ) AS anniversary_day,
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
			picture_id,
			picture_caption
		FROM ropc_family
			INNER JOIN ropc_family_member ON ropc_family.id = family_id '
		. $where
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
		$male = [];
		$female = [];
		foreach ( $family[ 'parents' ] as $parent )
		{
			if ( $parent[ 'family_role' ] == 'husband' )
			{
				$male = $parent;
			}
			else
			{
				$female = $parent;
			}
		}
		$family += ropc_generate_family_name( $male, $female );
	}
	
	
	return $families_processed;
}

/**
 * @brief
 *	Generates a name for family
 *
 * @param array $male
 *	The husband/father of a family, or the individual if a single male
 * @param array $female
 *	The wife/mother of a family, or the individual if a single female
 *
 * @retval array
 *	@code
 *	array(
 *		'name_fl' => 'John and Amy Smith',
 *		'name_lf' => 'Smith, John and Amy',
 *	)
 *	@endcode
 */
function ropc_generate_family_name( array $male, array $female )
{
	$family_name_lf = '';
	$family_name_fl = '';
	$male_ln = $male ? $male[ 'last_name' ] : '';
	$male_fn = $male ? $male[ 'first_name' ] : '';
	$female_ln = $female ? $female[ 'last_name' ] : '';
	$female_fn = $female ? $female[ 'first_name' ] : '';

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
	return [
		'name_fl' => $family_name_fl,
		'name_lf' => $family_name_lf,
	];
}

/**
 * @brief
 *	Gets all members with a birthday in the given month
 *
 * @param int $month_n
 *	The month's number (January = 1)
 *
 * @retval array
 *	Keys are days of the given month on which at least one member has a birthday; each key points to
 *	a string containing the name(s) of the member(s) whose birthday is on that day of the month
 *	@code
 *		1  => 'John Smith',
 *		21 => 'Amy Johnson, Rose Haywood',
 *	@endcode
 */ 
function ropc_get_birthdays( $month_n )
{
	global $wpdb;
	$records = $wpdb->get_results(
		$wpdb->prepare( '
		SELECT day(birthday) AS day, first_name, last_name
		FROM ropc_family_member
		WHERE month(birthday) = %d
		ORDER BY last_name, first_name ASC',
		$month_n )
	);
	
	$by_day = [];
	foreach ( $records as $record )
	{
		$day = $record->day;
		$name = "$record->first_name $record->last_name";
		if ( empty( $by_day[ $day ] ) )
		{
			$by_day[ $day ] = $name;
		}
		else
		{
			$by_day[ $day ] .= ", $name";
		}
	}
	return $by_day;
}

/**
 * @brief
 *	Gets all couples with an anniversary in the given month
 *
 * @param int $month_n
 *	The month's number (January = 1)
 *
 * @retval array
 *	Keys are days of the given month on which at least one couple has an anniversary; each key
 *	points to a string containing the name(s) of the couple(s) whose anniversary is on that day of
 *	the month
 *	@code
 *		1  => 'John Smith and Amy Smith',
 *		21 => 'Jack and Amy Johnson, George and Rose Maywood',
 *	@endcode
 */ 
function ropc_get_anniversaries( $month_n )
{
	$month_n = intval( $month_n );
	$familes = ropc_get_families( "month(anniversary) = '%s'", [ $month_n ] );
	
	$by_day = [];
	foreach ( $familes as $family )
	{
		$day = $family[ 'anniversary_day' ];
		$name = $family[ 'name_fl' ];
		if ( empty( $by_day[ $day ] ) )
		{
			$by_day[ $day ] = $name;
		}
		else
		{
			$by_day[ $day ] .= ", $name";
		}		
	}
	return $by_day;
}