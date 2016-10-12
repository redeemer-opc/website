<?php
/**
 * Template Name: Member Center
 *
 */

get_header();

?>

<div id="main-content">
This page is currently being developed.
<?php foreach ( ropc_get_families() as $family ): ?>
###	<pre><?php print_r( $family ) ?></pre>
<?php endforeach ?> 

</div> <!-- #main-content -->

<?php get_footer(); ?>
