<?php
/**
 * Single post.
 *
 * @package eateggs
 */

get_header();

while ( have_posts() ) :
	the_post();

	// Run the_content filters now so the TOC (built in eateggs_inject_heading_ids)
	// is populated before we render the sidebar.
	$eateggs_content = apply_filters( 'the_content', get_the_content() );
	$eateggs_content = str_replace( ']]>', ']]&gt;', $eateggs_content );

	$eateggs_cat      = eateggs_primary_category();
	$eateggs_min      = eateggs_read_time();
	$eateggs_distance = get_post_meta( get_the_ID(), 'distance', true );
	?>

<!-- ============ POST ============ -->
<article class="post">
	<div class="container">
	<div class="post-grid">

		<aside class="railbox">
		<dl>
			<div class="rl"><dt><?php esc_html_e( 'Published', 'eateggs' ); ?></dt><dd><?php echo esc_html( get_the_date() ); ?></dd></div>
			<?php if ( $eateggs_cat ) : ?>
			<div class="rl"><dt><?php esc_html_e( 'Category', 'eateggs' ); ?></dt><dd style="color:var(--cta-deep)"><a href="<?php echo esc_url( get_category_link( $eateggs_cat->term_id ) ); ?>"><?php echo esc_html( $eateggs_cat->name ); ?></a></dd></div>
			<?php endif; ?>
			<div class="rl"><dt><?php esc_html_e( 'Read time', 'eateggs' ); ?></dt><dd><?php echo esc_html( $eateggs_min ); ?> min</dd></div>
			<?php if ( $eateggs_distance ) : ?>
			<div class="rl"><dt><?php esc_html_e( 'Distance', 'eateggs' ); ?></dt><dd><?php echo esc_html( $eateggs_distance ); ?></dd></div>
			<?php endif; ?>
		</dl>
		<?php eateggs_render_toc(); ?>
		</aside>

		<div class="post-head">
		<div class="pmeta">
			<?php
			if ( $eateggs_cat ) :
				?>
				<span class="cat"><?php echo esc_html( $eateggs_cat->name ); ?></span><span class="sep">&middot;</span><?php endif; ?>
			<span>
			<?php
			/* translators: %s: post author name. */
			printf( esc_html__( 'By %s', 'eateggs' ), esc_html( get_the_author() ) );
			?>
			</span><span class="sep">&middot;</span><span><?php echo esc_html( get_the_date() ); ?></span>
		</div>
		<h1><?php the_title(); ?></h1>
		</div>

		<?php if ( has_post_thumbnail() ) : ?>
		<div class="post-hero"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php
			$eateggs_caption = wp_get_attachment_caption( get_post_thumbnail_id() );
			if ( $eateggs_caption ) :
				?>
		<p class="post-cap"><?php echo esc_html( $eateggs_caption ); ?></p>
			<?php endif; ?>
		<?php else : ?>
		<div class="post-hero"><span class="ph"><?php esc_html_e( 'Hero photo', 'eateggs' ); ?></span></div>
		<?php endif; ?>

		<div class="prose">
		<?php echo wp_kses_post( $eateggs_content ); ?>
		</div>

		<?php
		$eateggs_tags = get_the_tags();
		if ( $eateggs_tags ) :
			?>
		<div class="post-foot">
		<span class="lbl"><?php esc_html_e( 'Tagged', 'eateggs' ); ?></span>
			<?php foreach ( $eateggs_tags as $eateggs_tag ) : ?>
		<a class="tagpill" href="<?php echo esc_url( get_tag_link( $eateggs_tag->term_id ) ); ?>"><?php echo esc_html( $eateggs_tag->name ); ?></a>
		<?php endforeach; ?>
		</div>
		<?php endif; ?>

	</div>
	</div>
</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

endwhile;

get_footer();
