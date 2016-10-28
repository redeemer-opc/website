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
			<h2><?php echo $name_fl ?></h2>
		</td>
	</tr>
	<tr data-record-id="<?php echo $fam_id ?>">
		<td class="family-cell">
			<?php if ( $picture_id ): ?>
				<?php $pic_info = wp_get_attachment_image_src( $picture_id ) ?>
				<?php if ( is_array( $pic_info ) ): ?>
				<img src="<?php echo $pic_info[ 0 ] ?>"/>
				<?php endif ?>
			<?php endif ?>
			<form data-role="family-upload" method="post" action="#" enctype="multipart/form-data" >
				<input type="file" name="family_image">
				<input type="hidden" name="action" value="update_ropc_picture">
				<input type="hidden" name="family_id" value="<?php echo $fam_id ?>">
				<input name="upload-image" type="submit" value="upload">
			</form>
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
				<?php if ( $can_edit && count( $husband ) <= 1 ): ?>
					<i>Husband, or member info if single male:</i>
				<?php endif ?>
				<?php echo _member_center_display_person( $husband, $can_edit ) ?>
			<?php endif ?>
			<?php if ( $can_edit || $wife ): ?>
				<?php if ( $can_edit && count( $wife ) <= 1 ) : ?>
					<i>Wife, or member info if single female:</i>
				<?php endif ?>
				<?php echo _member_center_display_person( $wife, $can_edit ) ?>
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
	

	<div id="output1"></div>
	<script>
	jQuery(document).ready(function() { 
		var options = { 
			target:        '#output1',      // target element(s) to be updated with server response 
			beforeSubmit:  showRequest,     // pre-submit callback 
			success:       showResponse,    // post-submit callback 
			url:    ajaxurl                 // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php     
		}; 

		// bind form using 'ajaxForm' 
		jQuery('#thumbnail_upload').ajaxForm(options); 
	});
	function showRequest(formData, jqForm, options) {
	//do extra stuff before submit like disable the submit button
	jQuery('#output1').html('Sending...');
	jQuery('#submit-ajax').attr("disabled", "disabled");
	}
	function showResponse(responseText, statusText, xhr, $form)  {
			jQuery('#submit-ajax').attr("disabled", null);
	}
	</script>
</div> <!-- #main-content -->

<?php get_footer(); ?>
