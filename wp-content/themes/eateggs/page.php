<?php
/**
 * Static page template.
 *
 * @package eateggs
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
<article class="post">
	<div class="container">
	<div class="post-grid">
		<div class="post-head">
		<h1><?php the_title(); ?></h1>
		</div>
		<?php if ( has_post_thumbnail() ) : ?>
		<div class="post-hero"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>
		<div class="prose">
		<?php the_content(); ?>
		<?php
		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'eateggs' ),
				'after'  => '</div>',
			)
		);
		?>
		</div>
	</div>
	</div>
</article>
	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
endwhile;

get_footer();
