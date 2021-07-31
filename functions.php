<?php

namespace Theme\Breasy;

add_action( 'wp_head', __NAMESPACE__ . '\remove_duplicate_tags', -1 ); // Use -1 to target something registered at 0.
add_action( 'after_setup_theme', __NAMESPACE__ . '\register_support' );
add_action( 'after_setup_theme', __NAMESPACE__ . '\dequeue_global_styles' );
add_action( 'after_setup_theme', __NAMESPACE__ . '\requeue_theme_json_styles' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_theme_styles', 11 );

/**
 * Remove tags injected by the Gutenberg plugin.
 *
 * @see https://github.com/WordPress/gutenberg/pull/33797
 */
function remove_duplicate_tags() {
	remove_action( 'wp_head', 'gutenberg_viewport_meta_tag', 0 );
	remove_action( 'wp_head', 'gutenberg_render_title_tag', 1 );
}

/**
 * Register support for a handful of things that the theme may need to support.
 */
function register_support() {
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'editor-styles' );
}

/**
 * Stop both Gutenberg and WordPress from enqueuing default color palette and gradient
 * styles on every page view.
 *
 * This is probably a bad idea somehow.
 */
function dequeue_global_styles() {
	remove_action( 'wp_enqueue_scripts', 'gutenberg_experimental_global_styles_enqueue_assets' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
}

/**
 * Hack together the code from gutenberg_experimental_global_styles_enqueue_assets() so
 * that only the color palette and gradient properties defined in this theme's theme.json
 * file are enqueued.
 */
function requeue_theme_json_styles() {
	if ( function_exists( 'gutenberg_get_default_block_editor_settings' ) ) {
		$settings = gutenberg_get_default_block_editor_settings();
	} else {
		$settings = get_default_block_editor_settings();
	}

	if ( function_exists( 'gutenberg_experimental_global_styles_get_stylesheet' ) ) {
		$theme_support_data = \WP_Theme_JSON_Gutenberg::get_from_editor_settings( $settings );

		$result = new \WP_Theme_JSON_Gutenberg();
		$result->merge( \WP_Theme_JSON_Resolver_Gutenberg::get_theme_data( $theme_support_data ) );

		$stylesheet = gutenberg_experimental_global_styles_get_stylesheet( $result );
	} else {
		$theme_json = \WP_Theme_JSON_Resolver::get_merged_data( $settings );
		$stylesheet = $theme_json->get_stylesheet();
	}

	if ( empty( $stylesheet ) ) {
		return;
	}

	wp_register_style( 'global-styles', false, array(), true, true );
	wp_add_inline_style( 'global-styles', $stylesheet );
	wp_enqueue_style( 'global-styles' );
}

/**
 * Enqueue the theme's stylesheet.
 */
function enqueue_theme_styles() {
	wp_enqueue_style( 'breasy-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
}
