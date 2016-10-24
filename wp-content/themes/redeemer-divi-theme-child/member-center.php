<?php
/**
 * Template Name: Member Center
 *
 */

get_header();

error_reporting(E_ALL); ini_set("display_errors", 1);

?>

<?php function _member_center_display_person( array $person, $is_parent = TRUE )
{
	$can_edit = TRUE;
	$person += [
		'first_name' => '',
		'last_name' => '',
		'cellphone' => '',
		'occupation' => '',
		'email' => '',
		'birthday' => '',
		'anniversary' => '',
		'mem_id' => '',
		'family_role' => '',
	];
	extract( $person );

 /* BEGIN person template */ ?>

<div class="person" data-family-role="<?php echo $family_role ?>" data-record-id="<?php echo $mem_id ?>">
	<div class="person-name"><span data-placeholder="First name" data-edit-field="ropc_family_member.first_name"><?php echo $first_name ?></span>
		<span data-placeholder="Last name" data-edit-field="ropc_family_member.last_name"><?php echo $last_name ?></span>
	</div>
	<?php if ( $birthday || $can_edit ): ?>
		<div class="person-bd">Birthday: <span data-placeholder="Birthday" data-edit-field="ropc_family_member.birthday"><?php echo $birthday ? date( 'F d', strtotime( $birthday ) ) : '' ?></span></div>
	<?php endif ?>
	<?php if ( $is_parent && ( $occupation || $can_edit ) ): ?>
		<div class="person-occupation">Occupation: <span data-placeholder="Occupation" data-edit-field="ropc_family_member.occupation"><?php echo $occupation ?></span></div>
	<?php endif ?>
	<?php if ( $is_parent && ( $email || $can_edit ) ): ?>
		<div class="person-email">Email: <span data-placeholder="Email" data-edit-field="ropc_family_member.email"><?php echo $email ?></span></div>
	<?php endif ?>
	<?php if ( $is_parent && ( $cellphone || $can_edit ) ): ?>
		<div class="person-cell">Cellphone: <span data-placeholder="Cellphone" data-edit-field="ropc_family_member.cellphone"><?php echo $cellphone ?></span></div>
	<?php endif ?>
</div>

<? /* END person template */ } ?>

<?php function _member_center_display_family( array $family )
{
	$can_edit = TRUE;
	$family += [
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
	];
	extract( $family );

	$husband = [];
	$wife = [];
	foreach ( $parents as $parent )
	{
		if ( $parent[ 'family_role' ] == 'husband' )
		{
			$husband = $parent;
		}
		else
		{
			$wife = $parent;
		}
	}

 /* BEGIN family template */ ?>

<h2><?php echo $name_fl ?></h2>
<div class="css-table" data-record-id="<?php echo $fam_id ?>">
	<div class="css-row">
		<div class="css-cell family-cell">
			<?php if ( $can_edit || $address1 ) : ?>
				<span data-placeholder="Address line 1" data-edit-field="ropc_family.address1"><?php echo $address1 ?></span><br>
			<?php endif ?>
			<?php if ( $can_edit || $address2 ) : ?>
				<span data-placeholder="Address line 2" data-edit-field="ropc_family.address2"><?php echo $address2 ?></span><br>
			<?php endif ?>
			<?php if ( $can_edit || $city ) : ?>
				<span data-placeholder="City" data-edit-field="ropc_family.city"><?php echo $city ?></span>,
			<?php endif ?>
			<?php if ( $can_edit || $state ) : ?>
				<span data-placeholder="State" data-edit-field="ropc_family.state"><?php echo $state ?></span>,
			<?php endif ?>
			<?php if ( $can_edit || $zip ) : ?>
				<span data-placeholder="Zip" data-edit-field="ropc_family.zip"><?php echo $zip ?></span><br>
			<?php endif ?>
			<?php if ( $can_edit || $home_phone ) : ?>
				Home phone: <span data-placeholder="Home phone" data-edit-field="ropc_family.home_phone"><?php echo $home_phone ?></span>
			<?php endif ?>
			<?php if ( $can_edit || $anniversary ): ?>
				<div class="person-anniversary">Anniversary: <span data-placeholder="Anniversary" data-edit-field="ropc_family.anniversary"><?php echo $anniversary ? date( 'F d', strtotime( $anniversary ) ) : '' ?></span></div>
			<?php endif ?>
			</div>
		<div class="css-cell people-cell">
			<?php if ( $can_edit || $husband ): ?>
				<?php if ( $can_edit && ! $husband ): ?>
					<i>Husband, or member info if single male:</i>
				<?php endif ?>
				<?php echo _member_center_display_person( $husband ) ?>
			<?php endif ?>
			<?php if ( $can_edit || $wife ): ?>
				<?php if ( $can_edit && ! $wife ): ?>
					<i>Wife, or member info if single female:</i>
				<?php endif ?>
				<?php echo _member_center_display_person( $wife ) ?>
			<?php endif ?>
			<?php foreach ( $children as $child ): ?>
				<?php echo _member_center_display_person( $child, FALSE ) ?>
			<?php endforeach ?>
			<?php if ( $can_edit ): ?>
			<button class="button add-child">Add child</button>
			<div class="hidden child-template">
				<?php echo _member_center_display_person( [ 'family_role' => 'child' ], FALSE ) ?>
			</div>
			<?php endif ?>
		</div>
	</div>
</div><? /* END family template */ } ?>

<pre><?php echo print_r( ropc_get_families() ) ?></pre>

<div id="main-content">
This page is currently under development.
<?php foreach ( ropc_get_families() as $family ): ?>
	<?php echo _member_center_display_family( $family ); ?>
	<button class="button add-family">Add family</button>
	<div class="hidden family-template">
		<?php echo _member_center_display_family( [] ); ?>
	</div>
<?php endforeach ?>

</div> <!-- #main-content -->

<?php get_footer(); ?>
