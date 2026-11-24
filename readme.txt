=== Open File Library ===
Contributors: saplumbaga
Tags: files, documents, downloads, shortcode, file library
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Browse, search, and download public files from an existing WordPress uploads folder using a shortcode.

== Description ==

Open File Library is a lightweight WordPress plugin by Algoritmik. It displays local uploads folders and files as a responsive list with nested folder navigation, filename search, and direct download links. The filesystem is the source of truth, so there are no imports, attachment records, or saved plugin settings.

Use `[open_file_library folder="publications"]` to list PDFs. Set `search="true"` or `breadcrumbs="true"` to enable either option. Under Settings → Open File Library, the help page can generate a shortcode with these options and controls for file size, date, types, and sorting.

Supported extensions include PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ODT, ODS, ODP, TXT, CSV, ZIP, JPG, JPEG, PNG, GIF, and WEBP.

== Installation ==

1. Upload the installable ZIP through Plugins → Add New Plugin → Upload Plugin, or copy the plugin to `wp-content/plugins/open-file-library/`.
2. Activate Open File Library from the Plugins screen.
3. Create a directory beneath your site’s uploads base and add public files using SFTP, SSH, or your hosting file manager.
4. Add the shortcode to a post, page, or Shortcode block. The `folder` value is relative to uploads, such as `publications/reports`.

== Frequently Asked Questions ==

= Can I list private files? =

Use public files only. Files can remain directly accessible through the uploads URL even if they are excluded from the listing or the page is password-protected. Your hosting setup controls how files are served. Give shortcode-enabled publishing access to people you trust to select appropriate public uploads directories.

= Why are files missing from the list? =

Check the configured types, exact folder path, and file permissions. The plugin skips hidden entries, symbolic links, and unsafe file types. Each scan is limited to 16 descendant directory levels and 5,000 entries with a best-effort one-second budget. An incomplete library displays a notice. A full-page cache can delay updates until it expires or is purged.

= Can I use a CDN or remote storage? =

A public uploads URL can use a CDN when its paths still match a locally readable directory. Remote-only storage is unsupported.

= Why does the download link sometimes open the file? =

Browsers and server headers control how downloads behave, especially when the uploads URL uses a different origin.

== Changelog ==

= 1.0.0 =
* Browse public uploads folders, search filenames, and sort by name, size, or modification date.
* Generate shortcodes from the WordPress help page. Direct links remain available without JavaScript.
* Prevent main-site libraries from listing nested subsite uploads on multisite.
