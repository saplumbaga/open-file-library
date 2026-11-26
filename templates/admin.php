<?php

namespace OpenFileLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
function print_help_page( array $uploads, array $booleans, array $selects ): void {
	?>
	<div class="wrap ofl-admin">
		<h1><?php esc_html_e( 'Algoritmik Open File Library', 'algoritmik-open-file-library' ); ?></h1>
		<p><?php esc_html_e( 'Display a public folder of files from your WordPress uploads directory. Create the folder and add your files using SFTP, SSH, or your hosting file manager. Then add the shortcode to a page or post.', 'algoritmik-open-file-library' ); ?></p>

		<?php if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) : ?>
			<div class="notice notice-warning inline">
				<p><?php esc_html_e( 'WordPress could not provide a usable uploads directory. Check your site’s uploads settings and confirm that the directory is readable.', 'algoritmik-open-file-library' ); ?></p>
			</div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Shortcode generator', 'algoritmik-open-file-library' ); ?></h2>
		<div class="ofl-generator" hidden>
			<p><label for="ofl-folder"><?php esc_html_e( 'Folder relative to uploads', 'algoritmik-open-file-library' ); ?></label></p>
			<input id="ofl-folder" class="regular-text" type="text" placeholder="publications/reports" aria-describedby="ofl-folder-hint">
			<p id="ofl-folder-hint" class="description"><?php esc_html_e( 'Use forward slashes for subfolders and match the case on disk.', 'algoritmik-open-file-library' ); ?></p>

			<fieldset>
				<legend><?php esc_html_e( 'Display options', 'algoritmik-open-file-library' ); ?></legend>
				<?php foreach ( $booleans as $option => $label ) : ?>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $option ); ?>" data-ofl-option <?php checked( DEFAULTS[ $option ] ); ?>>
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</fieldset>

			<fieldset>
				<legend><?php esc_html_e( 'File types', 'algoritmik-open-file-library' ); ?></legend>
				<div class="ofl-types">
					<?php foreach ( FILE_TYPES as $extension ) : ?>
						<label>
							<input type="checkbox" name="types" value="<?php echo esc_attr( $extension ); ?>" <?php checked( DEFAULTS['types'], $extension ); ?>>
							<?php echo esc_html( strtoupper( $extension ) ); ?>
						</label>
					<?php endforeach; ?>
				</div>
				<p class="description"><?php esc_html_e( 'PDF is selected by default. Clearing all file types shows folders only.', 'algoritmik-open-file-library' ); ?></p>
			</fieldset>

			<?php foreach ( $selects as $option => $select ) : ?>
				<p>
					<label for="ofl-<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $select['label'] ); ?></label>
					<select id="ofl-<?php echo esc_attr( $option ); ?>" name="<?php echo esc_attr( $option ); ?>" data-ofl-option>
						<?php foreach ( $select['choices'] as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( DEFAULTS[ $option ], $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endforeach; ?>

			<p><label for="ofl-shortcode"><?php esc_html_e( 'Generated shortcode', 'algoritmik-open-file-library' ); ?></label></p>
			<textarea id="ofl-shortcode" class="large-text code" rows="2" readonly dir="ltr"></textarea>
			<p><button class="button button-secondary ofl-copy" type="button" disabled><?php esc_html_e( 'Copy shortcode', 'algoritmik-open-file-library' ); ?></button></p>
			<p class="ofl-copy-status" role="status"></p>
		</div>
		<noscript><p><?php esc_html_e( 'Use the examples below to write a shortcode by hand.', 'algoritmik-open-file-library' ); ?></p></noscript>

		<h2><?php esc_html_e( 'Examples', 'algoritmik-open-file-library' ); ?></h2>
		<p><?php esc_html_e( 'By default, the library lists PDFs by filename in ascending order and shows file sizes. Search, breadcrumbs, and modification dates are hidden.', 'algoritmik-open-file-library' ); ?></p>
		<p><code>[open_file_library folder="publications"]</code></p>
		<p><code>[open_file_library folder="publications/reports" search="true" breadcrumbs="true" show_size="false" show_date="true" types="pdf,docx,png" sort="date" order="desc"]</code></p>

		<h2><?php esc_html_e( 'Important notes', 'algoritmik-open-file-library' ); ?></h2>
		<ul class="ul-disc">
			<li><?php esc_html_e( 'Use public files only. Direct uploads URLs can remain accessible even if a file is omitted or the page is password-protected.', 'algoritmik-open-file-library' ); ?></li>
			<li><?php esc_html_e( 'If the library is unavailable, check that the path matches a readable folder beneath uploads and that the folder and its ancestors within uploads are not symbolic links.', 'algoritmik-open-file-library' ); ?></li>
			<li><?php esc_html_e( 'If external changes are missing, refresh the page cache or wait for it to expire.', 'algoritmik-open-file-library' ); ?></li>
		</ul>
	</div>
	<?php
}
