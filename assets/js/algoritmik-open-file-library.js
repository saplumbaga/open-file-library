(() => {
	'use strict';
	const { __, _n, sprintf } = wp.i18n;

	function enhance(library) {
		const browse = library.querySelector('.ofl-browse');
		const navigation = library.querySelector('.ofl-navigation');
		const breadcrumbs = library.querySelector('.ofl-breadcrumbs');
		const parentButton = library.querySelector('.ofl-parent');
		const input = library.querySelector('.ofl-search-input');
		const root = {
			list: browse.firstElementChild,
			name: __('Library root', 'algoritmik-open-file-library'),
			parent: null,
		};
		const folders = new Map([[root.list, root]]);
		const destinations = new WeakMap();
		const labels = [];
		let active = root;

		for (const row of root.list.querySelectorAll('.ofl-folder')) {
			const label = row.firstElementChild;
			const folder = {
				list: row.lastElementChild,
				name: label.querySelector('.ofl-name').textContent,
				parent: folders.get(row.parentElement),
				row,
			};
			const button = document.createElement('button');
			button.type = 'button';
			button.className = 'ofl-folder-label';
			button.append(...label.cloneNode(true).childNodes);
			folders.set(folder.list, folder);
			destinations.set(button, folder);
			labels.push({ label, button });
		}

		function updateNavigation() {
			parentButton.hidden = !active.parent;
			browse.setAttribute('aria-label', active.name);
			if (!breadcrumbs) {
				return;
			}
			const trail = [];
			for (let folder = active; folder; folder = folder.parent) {
				trail.push(folder);
			}
			breadcrumbs.replaceChildren(...trail.reverse().map((folder) => {
				const item = document.createElement('li');
				if (folder === active) {
					item.textContent = folder.name;
					item.setAttribute('aria-current', 'location');
				} else {
					const button = document.createElement('button');
					button.type = 'button';
					button.textContent = folder.name;
					destinations.set(button, folder);
					item.append(button);
				}
				return item;
			}));
		}

		library.addEventListener('click', (event) => {
			const button = event.target.closest('button');
			const folder = button === parentButton ? active.parent : destinations.get(button);
			if (!folder || folder === active) {
				return;
			}
			// Move the live list and return it to its original row when leaving the folder.
			if (active.row) {
				active.list.hidden = true;
				active.row.append(active.list);
			}
			active = folder;
			if (active.row) {
				browse.append(active.list);
				active.list.hidden = false;
			}
			root.list.hidden = active !== root;
			updateNavigation();
			browse.focus();
		});

		if (input) {
			const results = library.querySelector('.ofl-results');
			const status = library.querySelector('.ofl-status');
			const files = Array.from(root.list.querySelectorAll('.ofl-file'), (row) => ({
				name: row.querySelector('.ofl-name').textContent.normalize('NFC'),
				parent: row.dataset.oflParent || root.name,
				rank: Number(row.dataset.oflRank),
				row,
			})).sort((a, b) => a.rank - b.rank);
			let lastQuery = '';
			let frame;

			function updateSearch() {
				const query = input.value.trim().normalize('NFC');
				if (query === lastQuery) {
					return;
				}
				lastQuery = query;
				if (!query) {
					results.replaceChildren();
					results.hidden = true;
					status.textContent = '';
					navigation.hidden = false;
					browse.hidden = false;
					return;
				}
				const literal = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
				const matching = new RegExp(literal, 'iu');
				const rows = [];
				for (const file of files) {
					if (matching.test(file.name)) {
						const row = file.row.cloneNode(true);
						const parent = document.createElement('span');
						parent.className = 'ofl-result-parent';
						parent.textContent = file.parent;
						row.querySelector('.ofl-file-main').append(parent);
						rows.push(row);
					}
				}
				navigation.hidden = true;
				browse.hidden = true;
				results.replaceChildren(...rows);
				results.hidden = rows.length === 0;
				status.textContent = rows.length
					// translators: %d: Number of matching files.
					? sprintf(_n('%d file found.', '%d files found.', rows.length, 'algoritmik-open-file-library'), rows.length)
					: __('No matching files.', 'algoritmik-open-file-library');
			}

			input.addEventListener('input', () => {
				cancelAnimationFrame(frame);
				frame = requestAnimationFrame(updateSearch);
			});
		}

		for (const { label, button } of labels) {
			label.replaceWith(button);
		}
		updateNavigation();
		for (const list of folders.keys()) {
			list.hidden = list !== root.list;
		}
		navigation.hidden = false;
		if (input) {
			input.closest('label').hidden = false;
		}
	}

	function start() {
		document.querySelectorAll('.ofl-library').forEach(enhance);
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start, { once: true });
	} else {
		start();
	}
})();
