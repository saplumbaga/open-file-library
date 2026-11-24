<?php

namespace OpenFileLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function register_help_page(): void {
	$page = add_options_page(
		__( 'Open File Library', 'open-file-library' ),
		__( 'Open File Library', 'open-file-library' ),
		'manage_options',
		'open-file-library',
		__NAMESPACE__ . '\display_help_page'
	);
	if ( false !== $page ) {
		add_action( 'load-' . $page, __NAMESPACE__ . '\enqueue_help_assets' );
	}
}

function enqueue_help_assets(): void {
	wp_enqueue_style( 'ofl-admin', plugins_url( 'assets/css/admin.css', PLUGIN_FILE ), array(), VERSION );
	wp_enqueue_script( 'ofl-admin', plugins_url( 'assets/js/admin.js', PLUGIN_FILE ), array( 'wp-i18n' ), VERSION, true );
	wp_set_script_translations( 'ofl-admin', 'open-file-library' );
}

function display_help_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'open-file-library' ) );
	}

	$uploads = wp_upload_dir( null, false );
	$booleans = array(
		'search'      => __( 'Enable filename search', 'open-file-library' ),
		'breadcrumbs' => __( 'Show breadcrumbs', 'open-file-library' ),
		'show_size'   => __( 'Show file sizes', 'open-file-library' ),
		'show_date'   => __( 'Show modification dates', 'open-file-library' ),
	);
	$selects = array(
		'sort' => array(
			'label'   => __( 'Sort files by', 'open-file-library' ),
			'choices' => array(
				'title' => __( 'Filename', 'open-file-library' ),
				'date'  => __( 'Modification date', 'open-file-library' ),
				'size'  => __( 'File size', 'open-file-library' ),
			),
		),
		'order' => array(
			'label'   => __( 'Sort direction', 'open-file-library' ),
			'choices' => array(
				'asc'  => __( 'Ascending', 'open-file-library' ),
				'desc' => __( 'Descending', 'open-file-library' ),
			),
		),
	);
	require __DIR__ . '/../templates/admin.php';
}
