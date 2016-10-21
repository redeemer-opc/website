<?php
/**
 * Template Name: Member Center
 *
 */

get_header();

error_reporting(E_ALL); ini_set("display_errors", 1);

?>

<?php function _member_center_display_person( array $person )
{
	extract( $person );
 /* BEGIN person template */ ?>

<div class="person" data-record-id="<?php echo $mem_id ?>">
	<div " class="person-name"><span data-placeholder="First name" data-edit-field="ropc_family_member.first_name"><?php echo $first_name ?></span>
		<span data-placeholder="Last name" data-edit-field="ropc_family_member.last_name"><?php echo $last_name ?></span>
	</div>
	<?php if( $birthday ): ?>
		<div class="person-bd">Birthday: <span data-placeholder="Birthday" data-edit-field="ropc_family_member.birthday"><?php echo date( 'F d', strtotime( $birthday ) ) ?></span></div>
	<?php endif ?>
	<?php if ( $occupation ): ?>
		<div class="person-occupation">Occupation: <span data-placeholder="Occupation" data-edit-field="ropc_family_member.occupation"><?php echo $occupation ?></span></div>
	<?php endif ?>
	<?php if ( $email ): ?>
		<div class="person-email">Email: <span data-placeholder="Email" data-edit-field="ropc_family_member.email"><?php echo $email ?></span></div>
	<?php endif ?>
	<?php if ( $cellphone ): ?>
		<div class="person-cell">Cellphone: <span data-placeholder="Cellphone" data-edit-field="ropc_family_member.cellphone"><?php echo $cellphone ?></span></div>
	<?php endif ?>
</div>

<? /* END person template */ } ?>

<?php function _member_center_display_family( array $family )
{
	extract( $family );
 /* BEGIN family template */ ?>

<h2><?php echo $name_fl ?></h2>
<div class="css-table" data-record-id="<?php echo $fam_id ?>">
	<div class="css-row">
		<div class="css-cell family-cell">
			<span data-placeholder="Address line 1" data-edit-field="ropc_family.address1"><?php echo $address1 ?></span><br>
			<span data-placeholder="Address line 2" data-edit-field="ropc_family.address2"><?php echo $address2 ?></span><br>
			<span data-placeholder="City" data-edit-field="ropc_family.city"><?php echo $city ?></span>,
			<span data-placeholder="State" data-edit-field="ropc_family.state"><?php echo $state ?></span>,
			<span data-placeholder="Zip" data-edit-field="ropc_family.zip"><?php echo $zip ?></span><br>
			Home phone: <span data-placeholder="Home phone" data-edit-field="ropc_family.home_phone"><?php echo $home_phone ?></span>
			<?php if ( $anniversary ): ?>
				<div class="person-anniversary">Anniversary: <span data-placeholder="Anniversary" data-edit-field="ropc_family.anniversary"><?php echo date( 'F d', strtotime( $anniversary ) ) ?></span></div>
			<?php endif ?>
			</div>
		<div class="css-cell people-cell">
			<?php foreach ( $parents as $parent ): ?>
				<?php echo _member_center_display_person( $parent ) ?>
			<?php endforeach ?>
			<?php foreach ( $children as $child ): ?>
				<?php echo _member_center_display_person( $child ) ?>
			<?php endforeach ?>
		</div>
	</div>
</div><? /* END family template */ } ?>



<pre><?php echo print_r( ropc_get_families() ) ?></pre>

<div id="main-content">
This page is currently under development.
<?php foreach ( ropc_get_families() as $family ): ?>
	<?php echo _member_center_display_family( $family ); ?>
<?php endforeach ?>

</div> <!-- #main-content -->

<?php get_footer(); ?>
