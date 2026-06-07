<?php
/**
 * Flat-link navigation walker.
 *
 * @package eateggs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Nav walker that emits flat <a> elements (no <ul>/<li>), so a user-managed
 * menu matches the design's .nav-links > a styling. The current item gets the
 * "active" class used by the static design.
 */
class Eateggs_Link_Walker extends Walker_Nav_Menu {

	/**
	 * Start a sub-level. Intentionally a no-op so output stays flat.
	 *
	 * @param string        $output Menu output. Passed by reference.
	 * @param int           $depth  Depth of the menu item.
	 * @param stdClass|null $args   Optional. wp_nav_menu() arguments. Default null.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * End a sub-level. Intentionally a no-op so output stays flat.
	 *
	 * @param string        $output Menu output. Passed by reference.
	 * @param int           $depth  Depth of the menu item.
	 * @param stdClass|null $args   Optional. wp_nav_menu() arguments. Default null.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * End an element. Intentionally a no-op; start_el() emits a complete <a>.
	 *
	 * @param string        $output Menu output. Passed by reference.
	 * @param WP_Post       $item   Menu item data object.
	 * @param int           $depth  Depth of the menu item.
	 * @param stdClass|null $args   Optional. wp_nav_menu() arguments. Default null.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}

	/**
	 * Start (and finish) an element, emitting a flat <a>.
	 *
	 * @param string        $output Menu output. Passed by reference.
	 * @param WP_Post       $item   Menu item data object.
	 * @param int           $depth  Optional. Depth of the menu item. Default 0.
	 * @param stdClass|null $args   Optional. wp_nav_menu() arguments. Default null.
	 * @param int           $id     Optional. ID of the current menu item. Default 0.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = array();
		if ( in_array( 'current-menu-item', (array) $item->classes, true ) ) {
			$classes[] = 'active';
		}
		$class_attr = $classes ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : '';
		$url        = ! empty( $item->url ) ? $item->url : '#';
		$output    .= sprintf(
			'<a href="%1$s"%2$s>%3$s</a>',
			esc_url( $url ),
			$class_attr,
			esc_html( $item->title )
		);
	}
}
