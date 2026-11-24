# Open File Library

Open File Library is a lightweight WordPress plugin by Algoritmik that displays an existing uploads folder as a public, searchable library. It reads from the filesystem with zero imports and no plugin-specific database storage.

## Features

- Browse nested folders and search filenames across the library.
- Open available files directly or use the separate download link.
- Sort by filename, size or modification date, with optional file metadata.
- Use the nested list with JavaScript disabled, or place independent libraries on the same page.

## Installation

In WordPress, go to **Plugins → Add New Plugin → Upload Plugin**, upload `open-file-library.zip`, and activate it. For manual installation, copy the plugin into `wp-content/plugins/open-file-library/` and activate it from the Plugins screen. The minimum requirements are WordPress 6.5 and PHP 8.2.

## Quick Start

1. Create a folder such as `publications` beneath your site’s uploads directory.
2. Add files and subfolders with SFTP, SSH, or your hosting file manager.
3. Add the shortcode to a page or post. You can also generate it under **Settings → Open File Library**.

```text
publications/
├── Introduction.pdf
└── reports/
    └── Annual Report.pdf
```

```text
[open_file_library folder="publications"]

[open_file_library folder="publications/reports" search="true" breadcrumbs="true" show_size="false" show_date="true" types="pdf,docx,png" sort="date" order="desc"]
```

## Shortcode Options

| Option | Default | Values |
| --- | --- | --- |
| `folder` | Required | Directory relative to uploads, such as `publications/reports` |
| `search` | `false` | Set to `true` to show filename search. |
| `breadcrumbs` | `false` | Set to `true` to show the breadcrumb trail. Parent navigation stays available. |
| `show_size` | `true` | `true` or `false` |
| `show_date` | `false` | `true` or `false` |
| `types` | `pdf` | Comma-separated extensions from the list below |
| `sort` | `title` | `title`, `size` or `date` |
| `order` | `asc` | `asc` or `desc` |

Supported extensions are `pdf`, `doc`, `docx`, `xls`, `xlsx`, `ppt`, `pptx`, `odt`, `ods`, `odp`, `txt`, `csv`, `zip`, `jpg`, `jpeg`, `png`, `gif` and `webp`. A `types` selection with no supported extensions shows folders only. Invalid display or sort options fall back to their defaults.

## Important Notes

- **Use public files only.** Direct uploads URLs can remain accessible even when a file is excluded or the page is password-protected. Extension filtering does not validate file contents. Manage upload security and execution rules at the hosting level.
- Give shortcode-enabled publishing access to people you trust to select appropriate public uploads directories.
- The library needs a locally readable directory that maps to a public uploads URL. Symbolic links are skipped. Remote-only storage is unsupported.
- Each library scans up to 16 descendant directory levels and 5,000 entries, with a best-effort time budget of one second. An incomplete library displays a notice.
- File changes appear when WordPress renders the page again. Full-page caches may delay updates until expiry or purge.
- Browser policies and server headers determine whether the download link saves the file or opens it.

## License

Released under [GPL-2.0-or-later](LICENSE). Author [Algoritmik](https://algoritmik.net).
