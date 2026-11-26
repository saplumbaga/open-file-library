<?php

namespace OpenFileLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function register_help_page(): void {
	$page = add_options_page(
		__( 'Algoritmik Open File Library', 'algoritmik-open-file-library' ),
		__( 'Algoritmik Open File Library', 'algoritmik-open-file-library' ),
		'manage_options',
		'algoritmik-open-file-library',
		__NAMESPACE__ . '\display_help_page'
	);
	if ( false !== $page ) {
		add_action( 'load-' . $page, __NAMESPACE__ . '\enqueue_help_assets' );
	}
}

function enqueue_help_assets(): void {
	wp_enqueue_style( 'ofl-admin', plugins_url( 'assets/css/admin.css', PLUGIN_FILE ), array(), VERSION );
	wp_enqueue_script( 'ofl-admin', plugins_url( 'assets/js/admin.js', PLUGIN_FILE ), array( 'wp-i18n' ), VERSION, true );
	wp_set_script_translations( 'ofl-admin', 'algoritmik-open-file-library' );
}

function display_help_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'algoritmik-open-file-library' ) );
	}

	$uploads = wp_upload_dir( null, false );
	$booleans = array(
		'search'      => __( 'Enable filename search', 'algoritmik-open-file-library' ),
		'breadcrumbs' => __( 'Show breadcrumbs', 'algoritmik-open-file-library' ),
		'show_size'   => __( 'Show file sizes', 'algoritmik-open-file-library' ),
		'show_date'   => __( 'Show modification dates', 'algoritmik-open-file-library' ),
	);
	$selects = array(
		'sort' => array(
			'label'   => __( 'Sort files by', 'algoritmik-open-file-library' ),
			'choices' => array(
				'title' => __( 'Filename', 'algoritmik-open-file-library' ),
				'date'  => __( 'Modification date', 'algoritmik-open-file-library' ),
				'size'  => __( 'File size', 'algoritmik-open-file-library' ),
			),
		),
		'order' => array(
			'label'   => __( 'Sort direction', 'algoritmik-open-file-library' ),
			'choices' => array(
				'asc'  => __( 'Ascending', 'algoritmik-open-file-library' ),
				'desc' => __( 'Descending', 'algoritmik-open-file-library' ),
			),
		),
	);
	require_once __DIR__ . '/../templates/admin.php';
	print_help_page( $uploads, $booleans, $selects );
}
