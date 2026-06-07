<?php
/**
 * Main index / blog home: featured post + recent grid.
 *
 * @package eateggs
 */

get_header();
?>

<?php
// ---- Category strip (dynamic) -------------------------------------------
$eateggs_cats = get_categories(
	array(
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
if ( $eateggs_cats ) :
	?>
<div class="cat-strip">
	<div class="container row">
	<span class="lbl"><?php esc_html_e( 'Categories', 'eateggs' ); ?></span>
	<a class="chip<?php echo ( is_home() && ! is_category() ) ? ' on' : ''; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'All', 'eateggs' ); ?></a>
	<?php foreach ( $eateggs_cats as $eateggs_cat ) : ?>
		<a class="chip<?php echo is_category( $eateggs_cat->term_id ) ? ' on' : ''; ?>" href="<?php echo esc_url( get_category_link( $eateggs_cat->term_id ) ); ?>"><?php echo esc_html( $eateggs_cat->name ); ?> <span class="ct"><?php echo esc_html( $eateggs_cat->count ); ?></span></a>
	<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>

<!-- ============ HOME INDEX ============ -->
<main class="idx">
	<div class="container">

	<div class="idx-head">
		<h1><?php echo esc_html( eateggs_hero_text( 'heading' ) ); ?></h1>
		<p class="lede"><?php echo esc_html( eateggs_hero_text( 'lede' ) ); ?></p>
	</div>

	<?php
	if ( have_posts() ) :
		?>
	<div class="sec-head">
		<span class="sec-lbl"><?php esc_html_e( 'Latest', 'eateggs' ); ?></span>
		<span class="sec-rule" aria-hidden="true"></span>
	</div>
		<?php
		$eateggs_i = 0;
		while ( have_posts() ) :
			the_post();
			$eateggs_cat = eateggs_primary_category();
			$eateggs_min = eateggs_read_time();

			if ( 0 === $eateggs_i ) :
				// ---- Featured (first post) -----------------------------------
				?>
	<a class="feat" href="<?php the_permalink(); ?>">
		<div class="feat-shot">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'large', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
		<?php else : ?>
			<span class="ph"><?php esc_html_e( 'Featured photo', 'eateggs' ); ?></span>
		<?php endif; ?>
		</div>
		<div class="feat-body">
		<div class="pmeta">
				<?php
				if ( $eateggs_cat ) :
					?>
					<span class="cat"><?php echo esc_html( $eateggs_cat->name ); ?></span><span class="sep">&middot;</span><?php endif; ?>
			<span><?php echo esc_html( get_the_date() ); ?></span><span class="sep">&middot;</span><span class="read"><?php echo esc_html( $eateggs_min ); ?> min</span>
		</div>
		<h2><?php the_title(); ?></h2>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 40 ) ); ?></p>
		<span class="more"><?php esc_html_e( 'Read the report →', 'eateggs' ); ?></span>
		</div>
	</a>

	<div class="grid-head">
		<h3><?php esc_html_e( 'More recent', 'eateggs' ); ?></h3>
		<a class="all" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'All posts →', 'eateggs' ); ?></a>
	</div>
	<div class="pgrid">
				<?php
			else :
				// ---- Grid card ----------------------------------------------
				?>
		<a class="pcard" href="<?php the_permalink(); ?>">
		<div class="pcard-shot">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'medium', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
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
			<span class="read"><?php echo esc_html( $eateggs_min ); ?> min</span>
			</div>
			<h3><?php the_title(); ?></h3>
			<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
		</div>
		</a>
				<?php
			endif;
			++$eateggs_i;
		endwhile;

		// Close the grid only if we opened it (more than one post).
		if ( $eateggs_i > 1 ) :
			echo '</div>';
		endif;
		?>

	<div class="idx-foot">
		<?php
		$eateggs_older = get_next_posts_link( __( 'Load older posts', 'eateggs' ) );
		if ( $eateggs_older ) {
			// get_next_posts_link returns an <a>; add the design's button classes.
			echo wp_kses_post( str_replace( '<a ', '<a class="btn btn-load" ', $eateggs_older ) );
		}
		?>
	</div>

	<?php else : ?>
	<div class="idx-head">
		<p class="lede"><?php esc_html_e( 'No posts yet. Check back soon.', 'eateggs' ); ?></p>
	</div>
	<?php endif; ?>

	</div>
</main>

<?php
get_footer();
