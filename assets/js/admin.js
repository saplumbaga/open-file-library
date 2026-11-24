(() => {
	'use strict';
	const { __ } = wp.i18n;
	const generator = document.querySelector('.ofl-generator');
	const folder = generator.querySelector('#ofl-folder');
	const output = generator.querySelector('#ofl-shortcode');
	const copy = generator.querySelector('.ofl-copy');
	const status = generator.querySelector('.ofl-copy-status');
	const options = Array.from(generator.querySelectorAll('[data-ofl-option]'), (control) => ({
		control,
		defaultValue: control.type === 'checkbox'
			? String(control.defaultChecked)
			: Array.from(control.options).find((option) => option.defaultSelected).value,
	}));
	const types = Array.from(generator.querySelectorAll('[name="types"]'));
	const defaultTypes = types.filter((type) => type.defaultChecked).map((type) => type.value).join(',');

	function update() {
		const path = folder.value;
		const valid = path.split('/').every((part) => part && !part.startsWith('.') && !/[\\:\x00-\x1f\x7f]/.test(part));
		folder.setAttribute('aria-invalid', String(path !== '' && !valid));
		copy.disabled = !valid;
		status.textContent = '';
		if (!valid) {
			output.value = '';
			if (path) {
				status.textContent = __('Enter a relative folder path with no hidden folders or empty segments.', 'open-file-library');
			}
			return;
		}

		const encoded = path.replace(/[&"'<>[\]\u00a0\u200b]/g, (char) => `&#${char.charCodeAt(0)};`);
		const attributes = [`folder="${encoded}"`];
		for (const { control, defaultValue } of options) {
			const value = control.type === 'checkbox' ? String(control.checked) : control.value;
			if (value !== defaultValue) {
				attributes.push(`${control.name}="${value}"`);
			}
		}
		const selected = types.filter((type) => type.checked).map((type) => type.value).join(',');
		if (selected !== defaultTypes) {
			attributes.push(`types="${selected}"`);
		}
		output.value = `[open_file_library ${attributes.join(' ')}]`;
	}

	generator.addEventListener('input', (event) => {
		if (event.target !== output) {
			update();
		}
	});
	copy.addEventListener('click', async () => {
		const shortcode = output.value;
		try {
			await navigator.clipboard.writeText(shortcode);
			if (output.value === shortcode) {
				status.textContent = __('Shortcode copied.', 'open-file-library');
			}
		} catch {
			if (output.value === shortcode) {
				output.focus();
				output.select();
				status.textContent = __('Shortcode selected. Use your keyboard to copy it.', 'open-file-library');
			}
		}
	});
	update();
	generator.hidden = false;
})();
