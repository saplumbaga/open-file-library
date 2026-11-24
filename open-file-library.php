<?php
/**
 * Plugin Name: Open File Library
 * Description: A lightweight plugin by Algoritmik that displays physical folders and downloadable files using a shortcode. No imports or plugin-specific database storage.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.2
 * Author: Algoritmik
 * Author URI: https://algoritmik.net
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: open-file-library
 *
 * @package OpenFileLibrary
 */

namespace OpenFileLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VERSION = '1.0.0';
const PLUGIN_FILE = __FILE__;

require_once __DIR__ . '/includes/filesystem.php';
require_once __DIR__ . '/includes/shortcode.php';
require_once __DIR__ . '/templates/library.php';

add_action( 'init', __NAMESPACE__ . '\register_library' );

if ( is_admin() ) {
	require_once __DIR__ . '/includes/admin.php';
	add_action( 'admin_menu', __NAMESPACE__ . '\register_help_page' );
}
