<?php
/**
 * Template Name: Member Center
 *
 */

get_header();

/**
 * @brief
 *	Renders a person
 *
 * @param array $family (can be an empty array)
 *
 * @retval string
 *	The HTML to display the family
 */
function _member_center_display_person( array $person, $can_edit )
{
	$person += [
		'first_name' => '',
		'last_name' => '',
		'maiden_name' => '',
		'cellphone' => '',
		'occupation' => '',
		'email' => '',
		'birthday' => '',
		'anniversary' => '',
		'mem_id' => '',
		'family_role' => '',
	];
	extract( $person );

	$is_parent = in_array( $family_role, [ '', 'husband', 'wife' ] );
	
 /* BEGIN person template */ ?>

<div class="person" data-family-role="<?php echo $family_role ?>" data-record-id="<?php echo $mem_id ?>">
	<?php if ( $can_edit && !$mem_id ): ?>
		<?php if ( $family_role == 'husband' ): ?>
		<i>Husband, or member info if single male:</i>
		<?php elseif ( $family_role == 'wife' ): ?>
		<i>Wife, or member info if single female:</i>
		<?php endif ?>
	<?php endif ?>
	<div class="person-name"><span data-placeholder="First name" data-edit-field="ropc_family_member.first_name"><?php echo $first_name ?></span>
		<span data-placeholder="Last name" data-edit-field="ropc_family_member.last_name"><?php echo $last_name ?></span>
		<?php if ( $family_role == 'wife' && ( $maiden_name || $can_edit ) ): ?>
		<span class="maiden-name">(<span data-placeholder="Maiden name" data-edit-field="ropc_family_member.maiden_name"><?php echo $maiden_name ?></span>)</span>
		<?php endif ?>
		<?php if ( $can_edit ): ?>
			<span class="delete-person fa fa-trash"></span>
		<?php endif ?>
	</div>
	<?php if ( $birthday || $can_edit ): ?>
		<div class="person-bd">Birthday: <span data-placeholder="Birthday" data-edit-field="ropc_family_member.birthday"><?php echo $birthday ? date( 'F j', strtotime( $birthday ) ) : '' ?></span></div>
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

<?php
/**
 * @brief
 *	Renders an entire family
 *
 * @param array $family (can be an empty array)
 *
 * @retval string
 *	The HTML to display the family
 */
function _member_center_display_family( array $family, $can_edit )
{
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
		'picture_id' => 0,
		'picture_caption' => '',
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

	<tr>
		<td colspan=2>
			<h2><?php echo $name_fl ?>
			<?php if ( $can_edit ): ?>
				<span class="delete-family fa fa-trash"></span>
			<?php endif ?>
			</h2>
		</td>
	</tr>
	<tr data-record-id="<?php echo $fam_id ?>">
		<td class="family-cell">
			<?php if ( $can_edit || $picture_id ): ?>
				<?php $pic_info = wp_get_attachment_image_src( $picture_id ) ?>
				<img class="family-pic" src="<?php echo is_array( $pic_info ) ? $pic_info[ 0 ] : '' ?>"/>
			<?php endif ?>
			<?php if ( $can_edit ): ?>
			<form data-role="family-upload" method="post" action="#" enctype="multipart/form-data" >
				<label class="family-image button">
					<input type="file" class="hidden" name="family_image">
					<span class="label-text"><?php echo $picture_id ? "Change" : "Add" ?> picture</span>
				</label>	
				<input type="hidden" name="action" value="update_ropc_picture">
				<input type="hidden" name="family_id" value="<?php echo $fam_id ?>">
				
			</form>
			<?php endif ?>
			<?php if ( $can_edit || $picture_caption ): ?>
			<span class="picture-caption" data-placeholder="Picture caption" data-edit-field="ropc_family.picture_caption"><?php echo $picture_caption ?></span><br>
			<?php endif ?>
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
		</td>
		<td class="people-cell">
			<?php if ( $can_edit || $husband ): ?>
				<?php echo _member_center_display_person( $husband, $can_edit ) ?>
			<?php endif ?>
			<?php if ( $can_edit || $wife ): ?>
				<?php echo _member_center_display_person( $wife + [ 'family_role' => 'wife' ], $can_edit ) ?>
			<?php endif ?>
			<?php foreach ( $children as $child ): ?>
				<?php echo _member_center_display_person( $child, $can_edit ) ?>
			<?php endforeach ?>
			<?php if ( $can_edit ): ?>
			<button class="button add-child">Add child</button>
			<div class="hidden child-template">
				<?php echo _member_center_display_person( [ 'family_role' => 'child' ], TRUE ) ?>
			</div>
			<?php endif ?>
		</td>
	</tr>
<? /* END family template */ } ?>

<pre><?php //echo print_r( ropc_get_families() ) ?></pre>

<?php $can_edit = RopcFamilies::can_edit(); ?>
<div id="main-content">
	This page is currently under development.
	<table class="families-table">
	<?php foreach ( ropc_get_families() as $family ): ?>
		<?php echo _member_center_display_family( $family, $can_edit ); ?>
	<?php endforeach ?>
	<?php if ( $can_edit ): ?>
	</table>
	<br>
	<button class="button add-family">Add family</button>
	<table class="hidden family-template">
		<?php echo _member_center_display_family( [], TRUE ); ?>
	</table>
	<?php endif ?>
</div> <!-- #main-content -->

<?php get_footer(); ?>
