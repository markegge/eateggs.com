<?php
/**
 * Search results.
 *
 * @package eateggs
 */

get_header();
?>

<main class="idx">
	<div class="container">

	<div class="idx-head">
		<p class="eyebrow"><?php esc_html_e( 'Search', 'eateggs' ); ?></p>
		<h1>
		<?php
		/* translators: %s: search query. */
		printf( esc_html__( 'Results for “%s”', 'eateggs' ), esc_html( get_search_query() ) );
		?>
		</h1>
	</div>

	<?php if ( have_posts() ) : ?>
	<div class="pgrid">
		<?php
		while ( have_posts() ) :
			the_post();
			$eateggs_cat = eateggs_primary_category();
			?>
		<a class="pcard" href="<?php the_permalink(); ?>">
		<div class="pcard-shot">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'medium' ); ?>
			<?php else : ?>
			<span class="ph"><?php esc_html_e( 'Photo', 'eateggs' ); ?></span>
			<?php endif; ?>
		</div>
		<div class="pcard-body">
			<div class="pmeta">
			<?php
			if ( $eateggs_cat ) :
				?>
				<span class="cat"><?php echo esc_html( $eateggs_cat->name ); ?></span><span class="sep">&middot;</span><?php endif; ?>
			<span class="read"><?php echo esc_html( eateggs_read_time() ); ?> min</span>
			</div>
			<h3><?php the_title(); ?></h3>
			<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
		</div>
		</a>
			<?php
		endwhile;
		?>
	</div>
	<div class="idx-foot">
		<?php
		$eateggs_older = get_next_posts_link( __( 'Older results', 'eateggs' ) );
		if ( $eateggs_older ) {
			echo wp_kses_post( str_replace( '<a ', '<a class="btn btn-load" ', $eateggs_older ) );
		}
		?>
	</div>
	<?php else : ?>
	<p class="lede"><?php esc_html_e( 'Nothing matched. Try another search.', 'eateggs' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>

	</div>
</main>

<?php
get_footer();
