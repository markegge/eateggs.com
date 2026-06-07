<?php
/**
 * Eateggs theme functions.
 *
 * @package eateggs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'EATEGGS_VERSION', '1.0.0' );

/**
 * Theme setup.
 */
function eateggs_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 48,
			'width'       => 48,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'eateggs' ),
		)
	);
}
add_action( 'after_setup_theme', 'eateggs_setup' );

/**
 * Enqueue styles and scripts.
 *
 * Order matters: tokens.css defines the CSS variables and @font-face rules that
 * styles.css consumes, so it is enqueued first. tokens.css also references the
 * local font files via relative url(), which is why it stays in /assets next to
 * the /assets/fonts directory.
 */
function eateggs_assets() {
	$theme_uri = get_template_directory_uri();
	$ver       = EATEGGS_VERSION;

	// Newsreader (used for headings/serif accents in the design).
	wp_enqueue_style(
		'eateggs-google-fonts',
		'https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400;1,6..72,500&display=swap',
		array(),
		$ver
	);

	wp_enqueue_style( 'eateggs-tokens', $theme_uri . '/assets/tokens.css', array(), $ver );
	wp_enqueue_style( 'eateggs-styles', $theme_uri . '/assets/styles.css', array( 'eateggs-tokens' ), $ver );

	// WordPress requires the root style.css to be registered as the theme stylesheet.
	wp_enqueue_style( 'eateggs-style', get_stylesheet_uri(), array( 'eateggs-styles' ), $ver );

	// Scroll-spy for the single-post table of contents.
	if ( is_singular( 'post' ) ) {
		wp_enqueue_script( 'eateggs-scrollspy', $theme_uri . '/assets/scroll-spy.js', array(), $ver, true );
	}
}
add_action( 'wp_enqueue_scripts', 'eateggs_assets' );

// Flat-link nav walker for the header menu (no <ul>/<li>).
require_once get_template_directory() . '/inc/class-eateggs-link-walker.php';

/**
 * Estimate read time in minutes from a post's content.
 *
 * @param int|WP_Post|null $post Optional. Post to measure. Defaults to current.
 * @return int Minutes (minimum 1).
 */
function eateggs_read_time( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return 1;
	}
	$words   = str_word_count( wp_strip_all_tags( $post->post_content ) );
	$minutes = (int) ceil( $words / 200 );
	return max( 1, $minutes );
}

/**
 * Primary category for a post, as an object (or null).
 *
 * @param int|WP_Post|null $post Optional post.
 * @return WP_Term|null
 */
function eateggs_primary_category( $post = null ) {
	$cats = get_the_category( get_post( $post )->ID );
	if ( empty( $cats ) ) {
		return null;
	}
	return $cats[0];
}

$GLOBALS['eateggs_toc'] = array();

/**
 * Add slug ids to <h2>/<h3> headings in post content and collect them for a TOC.
 *
 * Stores the collected headings in a module-global so single.php can render the
 * sidebar table of contents before echoing the content.
 *
 * @param string $content The post content.
 * @return string The post content with ids injected into its headings.
 */
function eateggs_inject_heading_ids( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$GLOBALS['eateggs_toc'] = array();
	$used                   = array();

	$content = preg_replace_callback(
		'/<(h[23])\b([^>]*)>(.*?)<\/\1>/is',
		function ( $m ) use ( &$used ) {
			$tag   = strtolower( $m[1] );
			$attrs = $m[2];
			$inner = $m[3];
			$text  = trim( wp_strip_all_tags( $inner ) );

			// Reuse an existing id if the author set one; otherwise slug the text.
			if ( preg_match( '/\bid=["\']([^"\']+)["\']/', $attrs, $idm ) ) {
				$id = $idm[1];
			} else {
				$id = sanitize_title( $text );
				if ( '' === $id ) {
					$id = 'section';
				}
				$base = $id;
				$n    = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n;
					$n++;
				}
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}
			$used[ $id ] = true;

			$GLOBALS['eateggs_toc'][] = array(
				'id'    => $id,
				'text'  => $text,
				'level' => $tag,
			);

			return '<' . $tag . $attrs . '>' . $inner . '</' . $tag . '>';
		},
		$content
	);

	return $content;
}
add_filter( 'the_content', 'eateggs_inject_heading_ids', 7 );

/**
 * Render the collected TOC as the design's <nav class="toc"> markup.
 * Call AFTER the_content() has run (or after applying the_content filters).
 */
function eateggs_render_toc() {
	$items = $GLOBALS['eateggs_toc'];
	if ( empty( $items ) ) {
		return;
	}
	echo '<nav class="toc" aria-label="On this page">';
	echo '<p class="toc-lbl">' . esc_html__( 'On this page', 'eateggs' ) . '</p>';
	$first = true;
	foreach ( $items as $item ) {
		printf(
			'<a class="tl%1$s" href="#%2$s">%3$s</a>',
			$first ? ' cur' : '',
			esc_attr( $item['id'] ),
			esc_html( $item['text'] )
		);
		$first = false;
	}
	echo '</nav>';
}
