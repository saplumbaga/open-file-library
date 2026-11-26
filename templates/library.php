<?php

namespace OpenFileLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function print_library( array $library, array $options, string $base_url ): void {
	$root_url = $base_url . '/' . encode_path( $options['folder'] );
	?>
	<section class="ofl-library" aria-label="<?php esc_attr_e( 'File library', 'algoritmik-open-file-library' ); ?>">
		<?php if ( $library['incomplete'] ) : ?>
			<p class="ofl-notice"><?php esc_html_e( 'This library is incomplete. Some folders or files could not be listed.', 'algoritmik-open-file-library' ); ?></p>
		<?php endif; ?>
		<?php if ( $options['search'] ) : ?>
			<label class="ofl-search" hidden>
				<?php esc_html_e( 'Search files', 'algoritmik-open-file-library' ); ?>
				<input class="ofl-search-input" type="search">
			</label>
		<?php endif; ?>
		<div class="ofl-navigation" hidden>
			<?php if ( $options['breadcrumbs'] ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Library path', 'algoritmik-open-file-library' ); ?>">
					<ol class="ofl-breadcrumbs"></ol>
				</nav>
			<?php endif; ?>
			<button class="ofl-parent" type="button" hidden><?php esc_html_e( 'Up one folder', 'algoritmik-open-file-library' ); ?></button>
		</div>
		<div class="ofl-browse" role="group" tabindex="-1">
			<?php print_library_list( $library['tree'], $options, $root_url, $library['ranks'] ); ?>
		</div>
		<?php if ( $options['search'] ) : ?>
			<p class="ofl-status" role="status"></p>
			<ul class="ofl-results ofl-list" hidden></ul>
		<?php endif; ?>
	</section>
	<?php
}

function encode_path( string $path ): string {
	return implode( '/', array_map( 'rawurlencode', explode( '/', $path ) ) );
}

function print_library_list( array $tree, array $options, string $root_url, array $ranks, string $parent = '' ): void {
	?>
	<ul class="ofl-list">
		<?php if ( ! $tree['folders'] && ! $tree['files'] ) : ?>
			<li class="ofl-empty"><?php esc_html_e( 'This folder has no listed files or folders.', 'algoritmik-open-file-library' ); ?></li>
		<?php endif; ?>
		<?php foreach ( $tree['folders'] as $folder ) : ?>
			<li class="ofl-folder">
				<span class="ofl-folder-label">
					<?php print_library_icon( 'folder' ); ?>
					<span class="ofl-name"><?php echo esc_html( str_replace( '&', '&amp;', $folder['name'] ) ); ?></span>
				</span>
				<?php print_library_list( $folder['children'], $options, $root_url, $ranks, $folder['path'] ); ?>
			</li>
		<?php endforeach; ?>
		<?php foreach ( $tree['files'] as $file ) : ?>
			<?php
			$url = $root_url . '/' . encode_path( $file['path'] );
			$name = str_replace( '&', '&amp;', $file['name'] );
			/* translators: %s: file name. */
			$download_name = sprintf( __( 'Download %s', 'algoritmik-open-file-library' ), $name );
			?>
			<li class="ofl-file"<?php if ( $options['search'] ) : ?> data-ofl-rank="<?php echo esc_attr( (string) $ranks[ $file['path'] ] ); ?>" data-ofl-parent="<?php echo esc_attr( str_replace( '&', '&amp;', $parent ) ); ?>"<?php endif; ?>>
				<div class="ofl-file-main">
					<a class="ofl-open" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php print_library_icon( $file['type'] ); ?>
						<span class="ofl-name"><?php echo esc_html( $name ); ?></span>
					</a>
				</div>
				<?php if ( $options['show_size'] && null !== $file['size'] ) : ?>
					<span class="ofl-meta"><?php echo esc_html( size_format( $file['size'] ) ); ?></span>
				<?php endif; ?>
				<?php if ( $options['show_date'] && null !== $file['date'] ) : ?>
					<time class="ofl-meta" datetime="<?php echo esc_attr( wp_date( 'Y-m-d', $file['date'] ) ); ?>"><?php echo esc_html( wp_date( get_option( 'date_format' ), $file['date'] ) ); ?></time>
				<?php endif; ?>
				<a class="ofl-download" href="<?php echo esc_url( $url ); ?>" download aria-label="<?php echo esc_attr( $download_name ); ?>">
					<?php print_library_icon( 'download' ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

function print_library_icon( string $type ): void {
	?>
	<svg class="ofl-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
		<?php switch ( $type ) :
			case 'folder': ?>
				<path d="M3 6h7l2 2h9v11H3z"/>
				<?php break;
			case 'download': ?>
				<path d="M12 3v12m-4-4 4 4 4-4M4 16v5h16v-5"/>
				<?php break;
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
			case 'webp': ?>
				<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8" cy="9" r="1"/><path d="m4 17 5-5 3 3 4-4 5 5"/>
				<?php break;
			case 'zip': ?>
				<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M12 3v2m1 2v2m-1 2v2m-2 2h4v3h-4z"/>
				<?php break;
			default: ?>
				<path d="M6 3h8l4 4v14H6zM14 3v5h4m-9 5h6m-6 4h6"/>
		<?php endswitch; ?>
	</svg>
	<?php
}
