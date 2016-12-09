<?php
/**
 * Template Name: Member Center
 *
 */

if ( ! RopcFamilies::can_view() )
{
	wp_redirect( '/members' );
	exit;
}

get_header() ?>

<?php echo RopcFamilies::display_member_center_page() ?>

<?php get_footer(); ?>
