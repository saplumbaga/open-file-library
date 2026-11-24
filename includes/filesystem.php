<?php

namespace OpenFileLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const FILE_TYPES = array( 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'txt', 'csv', 'zip', 'jpg', 'jpeg', 'png', 'gif', 'webp' );
const MAX_DEPTH = 16;
const MAX_ENTRIES = 5000;
const SCAN_BUDGET_MS = 1000;
const BLOCKED_SUFFIXES = array(
	'pht', 'phtm', 'phtml', 'phps', 'phpt', 'phar', 'inc', 'cgi', 'fcgi', 'pl', 'py', 'rb', 'sh', 'bash', 'zsh', 'ksh',
	'asp', 'aspx', 'ashx', 'asmx', 'jsp', 'jspx', 'cfm', 'cfml', 'htm', 'html', 'xhtml', 'shtm', 'shtml', 'svg', 'svgz', 'js', 'mjs', 'cjs', 'wasm',
	'exe', 'dll', 'so', 'jar', 'com', 'bat', 'cmd', 'msi', 'ps1', 'vbs', 'wsf', 'ini', 'conf', 'config', 'env', 'htaccess', 'htpasswd', 'pem', 'key', 'pfx', 'sql',
);

function valid_segment( string $name ): bool {
	if ( '' === $name || '.' === $name[0] || 1 !== preg_match( '//u', $name ) ) {
		return false;
	}

	return false === strpbrk( $name, '/\\:' ) && ! preg_match( '/[\x00-\x1f\x7f]/', $name )
		&& ( 'Windows' !== PHP_OS_FAMILY || ! str_ends_with( $name, '.' ) && ! str_ends_with( $name, ' ' ) );
}

function contains_path( string $directory, string $path ): bool {
	return str_starts_with( $path, $directory . '/' );
}

/** Accept only the canonical entry at this exact path. Links can point inside the root too. */
function entry_stat( string $path, string $boundary ): ?array {
	clearstatcache( true, $path );
	$stat = @lstat( $path );
	if ( false === $stat || ! in_array( $stat['mode'] & 0170000, array( 0040000, 0100000 ), true ) ) {
		return null;
	}

	$canonical = @realpath( $path );
	if ( false === $canonical || wp_normalize_path( $canonical ) !== $path || ! contains_path( $boundary, $path ) ) {
		return null;
	}
	return $stat;
}

function library_location( string $folder ): ?array {
	$segments = explode( '/', $folder );
	foreach ( $segments as $segment ) {
		if ( ! valid_segment( $segment ) ) {
			return null;
		}
	}

	$uploads = wp_upload_dir( null, false );
	if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) {
		return null;
	}

	$url = wp_parse_url( $uploads['baseurl'] );
	if ( empty( $url['host'] ) || ! in_array( strtolower( $url['scheme'] ?? '' ), array( 'http', 'https' ), true )
		|| array_intersect_key( $url, array_flip( array( 'user', 'pass', 'query', 'fragment' ) ) ) ) {
		return null;
	}

	$canonical = @realpath( $uploads['basedir'] );
	if ( false === $canonical ) {
		return null;
	}
	$base = rtrim( wp_normalize_path( $canonical ), '/' );
	if ( '' === $base || ! @is_dir( $base ) ) {
		return null;
	}

	$root = $base;
	foreach ( $segments as $segment ) {
		$root .= '/' . $segment;
		$stat = entry_stat( $root, $base );
		if ( null === $stat || 0040000 !== ( $stat['mode'] & 0170000 ) ) {
			return null;
		}
	}

	return array( 'root' => $root, 'base_url' => rtrim( $uploads['baseurl'], '/' ) );
}

function allowed_extension( string $name, array $types ): ?string {
	$parts = explode( '.', strtolower( $name ) );
	if ( count( $parts ) < 2 ) {
		return null;
	}

	$extension = array_pop( $parts );
	if ( ! in_array( $extension, $types, true ) ) {
		return null;
	}

	// Some server configurations treat an intermediate suffix as a handler.
	$stem = array_shift( $parts );
	if ( in_array( $stem, array( 'wp-config', 'htaccess', 'htpasswd', 'passwd', 'shadow', 'id_rsa', 'id_ed25519' ), true ) ) {
		return null;
	}
	foreach ( $parts as $part ) {
		$part = trim( $part );
		if ( in_array( $part, BLOCKED_SUFFIXES, true ) || preg_match( '/^php[0-9]*$/', $part ) ) {
			return null;
		}
	}

	return $extension;
}

function natural_title( string $left, string $right ): int {
	if ( function_exists( 'mb_convert_case' ) ) {
		return strnatcmp( mb_convert_case( $left, MB_CASE_FOLD, 'UTF-8' ), mb_convert_case( $right, MB_CASE_FOLD, 'UTF-8' ) );
	}

	return strnatcasecmp( $left, $right );
}

function file_order( array $options ): \Closure {
	$sort = $options['sort'];
	$direction = 'desc' === $options['order'] ? -1 : 1;

	return static function ( array $left, array $right ) use ( $sort, $direction ): int {
		$title = natural_title( $left['name'], $right['name'] );
		if ( 'title' === $sort ) {
			return $direction * $title ?: strcmp( $left['path'], $right['path'] );
		}

		$a = $left[ $sort ];
		$b = $right[ $sort ];
		if ( ( null === $a ) !== ( null === $b ) ) {
			return ( null === $a ) <=> ( null === $b );
		}

		return $direction * ( $a <=> $b ) ?: $title ?: strcmp( $left['path'], $right['path'] );
	};
}

function scan_library( string $root, array $options ): ?array {
	$scan = array(
		'root'       => $root,
		'deadline'   => hrtime( true ) + SCAN_BUDGET_MS * 1_000_000,
		'visited'    => 0,
		'incomplete' => false,
		'search'     => array(),
		'file_order' => file_order( $options ),
	);
	$tree = scan_directory( '', 0, $options, $scan );
	if ( null === $tree ) {
		return null;
	}

	usort( $scan['search'], $scan['file_order'] );
	return array(
		'tree'       => $tree,
		'incomplete' => $scan['incomplete'],
		'ranks'      => array_flip( array_column( $scan['search'], 'path' ) ),
	);
}

function scan_directory( string $relative, int $depth, array $options, array &$scan ): ?array {
	$directory = '' === $relative ? $scan['root'] : $scan['root'] . '/' . $relative;
	$handle = @opendir( $directory );
	if ( false === $handle ) {
		$scan['incomplete'] = true;
		return null;
	}

	$folders = array();
	$files = array();
	try {
		while ( true ) {
			if ( $scan['visited'] >= MAX_ENTRIES || hrtime( true ) >= $scan['deadline'] ) {
				$scan['incomplete'] = true;
				break;
			}
			$name = @readdir( $handle );
			if ( false === $name ) {
				break;
			}
			if ( '.' === $name || '..' === $name ) {
				continue;
			}
			++$scan['visited'];
			if ( ! valid_segment( $name ) ) {
				continue;
			}

			$path = '' === $relative ? $name : $relative . '/' . $name;
			$stat = entry_stat( $directory . '/' . $name, $scan['root'] );
			if ( null === $stat ) {
				continue;
			}
			if ( 0040000 === ( $stat['mode'] & 0170000 ) ) {
				if ( $depth >= MAX_DEPTH || hrtime( true ) >= $scan['deadline'] ) {
					$scan['incomplete'] = true;
					continue;
				}
				$children = scan_directory( $path, $depth + 1, $options, $scan );
				if ( null !== $children ) {
					$folders[] = array( 'name' => $name, 'path' => $path, 'children' => $children );
				}
				continue;
			}

			$extension = allowed_extension( $name, $options['types'] );
			if ( null === $extension ) {
				continue;
			}
			if ( ! @is_readable( $directory . '/' . $name ) ) {
				$scan['incomplete'] = true;
				continue;
			}
			$file = array(
				'name' => $name,
				'path' => $path,
				'type' => $extension,
				'size' => isset( $stat['size'] ) && $stat['size'] >= 0 ? $stat['size'] : null,
				'date' => $stat['mtime'] ?? null,
			);
			$files[] = $file;
			if ( $options['search'] ) {
				$scan['search'][] = $file;
			}
		}
	} finally {
		closedir( $handle );
	}

	$direction = 'title' === $options['sort'] && 'desc' === $options['order'] ? -1 : 1;
	usort( $folders, static fn ( array $a, array $b ): int => $direction * ( natural_title( $a['name'], $b['name'] ) ?: strcmp( $a['name'], $b['name'] ) ) );
	usort( $files, $scan['file_order'] );
	return array( 'folders' => $folders, 'files' => $files );
}
