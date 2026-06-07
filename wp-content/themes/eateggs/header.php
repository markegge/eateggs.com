<?php
/**
 * Header: opens the document and renders the site header.
 *
 * @package eateggs
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/brand-eateggs.svg' ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ============ HEADER ============ -->
<header class="s-head">
	<div class="container nav">
	<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) . ' — home' ); ?>">
		<img class="brand-mark" src="<?php echo esc_url( get_template_directory_uri() . '/assets/brand-eateggs.svg' ); ?>" alt="">
		<span class="brand-word"><?php bloginfo( 'name' ); ?></span>
		<?php if ( get_bloginfo( 'description' ) ) : ?>
		<span class="brand-tag"><?php bloginfo( 'description' ); ?></span>
		<?php endif; ?>
	</a>
	<nav class="nav-links" aria-label="<?php esc_attr_e( 'Primary', 'eateggs' ); ?>">
		<?php
		/*
		* The design styles direct <a> children of .nav-links (including a CTA
		* button and a search glyph), so we render a custom menu rather than
		* wp_nav_menu()'s <ul><li> markup. Assign a menu to the "Primary Menu"
		* location to drive the first set of links from Appearance > Menus;
		* otherwise these sensible defaults are shown.
		*/
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'depth'          => 1,
					'walker'         => new Eateggs_Link_Walker(),
				)
			);
		} else {
			printf( '<a href="%s"%s>%s</a>', esc_url( home_url( '/' ) ), ( is_home() || is_front_page() ) ? ' class="active"' : '', esc_html__( 'Latest', 'eateggs' ) );
		}
		?>
		<a class="btn btn-cta btn-sm" href="<?php echo esc_url( home_url( '/subscribe/' ) ); ?>"><?php esc_html_e( 'Subscribe', 'eateggs' ); ?></a>
		<span class="nav-search" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.5" y2="16.5"></line></svg></span>
	</nav>
	</div>
</header>
