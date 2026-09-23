(function () {
	'use strict';

	function normalize(value) {
		return String(value || '').toLowerCase();
	}

	function init(container) {
		var search = container.querySelector('[data-art-gallery-related-products-search]');
		var rows = container.querySelectorAll('[data-art-gallery-related-product-row]');

		if (!search || rows.length === 0) {
			return;
		}

		search.addEventListener('input', function () {
			var query = normalize(search.value);

			rows.forEach(function (row) {
				var title = normalize(row.getAttribute('data-art-gallery-related-product-title'));

				row.hidden = query !== '' && title.indexOf(query) === -1;
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-art-gallery-related-products]').forEach(init);
	});
}());
