<?php
/**
 * 404 not found.
 *
 * @package eateggs
 */

get_header();
?>

<main class="idx">
	<div class="container">
	<div class="idx-head">
		<p class="eyebrow"><?php esc_html_e( '404', 'eateggs' ); ?></p>
		<h1><?php esc_html_e( 'That trail dead-ends.', 'eateggs' ); ?></h1>
		<p class="lede"><?php esc_html_e( 'The page you were after isn’t here. Try a search, or head back to the latest posts.', 'eateggs' ); ?></p>
	</div>
	<div class="idx-foot">
		<a class="btn btn-load" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Latest', 'eateggs' ); ?></a>
	</div>
	<?php get_search_form(); ?>
	</div>
</main>

<?php
get_footer();
