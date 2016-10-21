<?php
/**
 * Template Name: Members - From Plugin
 *
 */
if (is_user_logged_in())
{
	wp_redirect('/member-center/');
	exit;
}

get_header();

?>

<div id="main-content">
	Hello, world!
</div> <!-- #main-content -->

<?php get_footer(); ?>
