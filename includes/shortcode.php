<?php

namespace OpenFileLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DEFAULTS = array(
	'folder'      => '',
	'search'      => false,
	'breadcrumbs' => false,
	'show_size'   => true,
	'show_date'   => false,
	'types'       => 'pdf',
	'sort'        => 'title',
	'order'       => 'asc',
);

function register_library(): void {
	add_shortcode( 'open_file_library', __NAMESPACE__ . '\library_shortcode' );
	add_filter( 'pre_do_shortcode_tag', __NAMESPACE__ . '\reject_shortcode_backslashes', 10, 4 );

	wp_register_style( 'ofl-library', plugins_url( 'assets/css/open-file-library.css', PLUGIN_FILE ), array(), VERSION );
	wp_register_script( 'ofl-library', plugins_url( 'assets/js/open-file-library.js', PLUGIN_FILE ), array( 'wp-i18n' ), VERSION, true );
	wp_set_script_translations( 'ofl-library', 'open-file-library' );
}

function unavailable_library(): string {
	return '<p class="ofl-unavailable">' . esc_html__( 'The file library is unavailable.', 'open-file-library' ) . '</p>';
}

function reject_shortcode_backslashes( $output, string $tag, array $attributes, array $match ) {
	// WordPress passes the raw attribute text here, alongside the unescaped values.
	if ( false === $output && 'open_file_library' === $tag && str_contains( $match[3], '\\' ) ) {
		return unavailable_library();
	}
	return $output;
}

function shortcode_options( array $attributes ): array {
	$options = shortcode_atts( DEFAULTS, $attributes );
	foreach ( array( 'search', 'breadcrumbs', 'show_size', 'show_date' ) as $option ) {
		$options[ $option ] = match ( strtolower( trim( (string) $options[ $option ] ) ) ) {
			'true'  => true,
			'false' => false,
			default => DEFAULTS[ $option ],
		};
	}
	foreach ( array( 'sort' => array( 'title', 'date', 'size' ), 'order' => array( 'asc', 'desc' ) ) as $option => $allowed ) {
		$value = strtolower( trim( $options[ $option ] ) );
		$options[ $option ] = in_array( $value, $allowed, true ) ? $value : DEFAULTS[ $option ];
	}

	$types = array_map( 'trim', explode( ',', strtolower( $options['types'] ) ) );
	$options['types'] = array_intersect( FILE_TYPES, $types );
	$options['folder'] = html_entity_decode( $options['folder'], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	return $options;
}

function library_shortcode( array $attributes ): string {
	foreach ( array_keys( $attributes ) as $key ) {
		if ( is_int( $key ) ) {
			return unavailable_library();
		}
	}

	$options = shortcode_options( $attributes );
	$location = library_location( $options['folder'] );
	if ( null === $location ) {
		return unavailable_library();
	}
	$library = scan_library( $location['root'], $options, $location['excluded'] );
	if ( null === $library ) {
		return unavailable_library();
	}

	wp_enqueue_style( 'ofl-library' );
	wp_enqueue_script( 'ofl-library' );

	ob_start();
	print_library( $library, $options, $location['base_url'] );
	return ob_get_clean();
}
