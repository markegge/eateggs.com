<?php
/**
 * Theme Customizer settings.
 *
 * @package eateggs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Default copy for the homepage hero.
 *
 * Defined once so the Customizer setting defaults and the template fallbacks
 * cannot drift apart.
 *
 * @return array{heading:string,lede:string} Default hero strings.
 */
function eateggs_hero_defaults() {
	return array(
		'heading' => __( 'Notes from the saddle, the kitchen, and Bozeman.', 'eateggs' ),
		'lede'    => __( 'Long-form trip reports, route beta, and the occasional sourdough post-mortem.', 'eateggs' ),
	);
}

/**
 * Get a homepage hero string, falling back to the shared default.
 *
 * @param string $key Hero text key, either 'heading' or 'lede'.
 * @return string The customized value, or the default if unset.
 */
function eateggs_hero_text( $key ) {
	$defaults = eateggs_hero_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	return get_theme_mod( "eateggs_hero_$key", $default );
}

/**
 * Register Customizer sections, settings, and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 * @return void
 */
function eateggs_customize_register( $wp_customize ) {
	$defaults = eateggs_hero_defaults();

	$wp_customize->add_section(
		'eateggs_hero',
		array(
			'title'    => __( 'Homepage Hero', 'eateggs' ),
			'priority' => 20,
		)
	);

	$wp_customize->add_setting(
		'eateggs_hero_heading',
		array(
			'default'           => $defaults['heading'],
			'transport'         => 'refresh',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'eateggs_hero_heading',
		array(
			'label'   => __( 'Hero heading', 'eateggs' ),
			'section' => 'eateggs_hero',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'eateggs_hero_lede',
		array(
			'default'           => $defaults['lede'],
			'transport'         => 'refresh',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'eateggs_hero_lede',
		array(
			'label'   => __( 'Hero lede', 'eateggs' ),
			'section' => 'eateggs_hero',
			'type'    => 'textarea',
		)
	);
}
add_action( 'customize_register', 'eateggs_customize_register' );
