(function ($) {
	'use strict';

	var settings = window.artGalleryMediaEditorSettings || {};
	var labels = settings.labels || {};
	var idCounter = 0;

	function label(key, fallback) {
		return labels[key] || fallback;
	}

	function generateItemId() {
		idCounter += 1;

		return [
			'media',
			Date.now().toString(36),
			Math.random().toString(36).slice(2, 10),
			String(idCounter)
		].join('_');
	}

	function escapeHtml(value) {
		return String(value || '').replace(/[&<>"']/g, function (character) {
			return {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				'\'': '&#039;'
			}[character];
		});
	}

	function normalizeItem(item) {
		item = item || {};

		return {
			id: String(item.id || generateItemId()),
			type: String(item.type || ''),
			attachment_id: item.attachment_id ? parseInt(item.attachment_id, 10) : 0,
			url: String(item.url || ''),
			title: String(item.title || ''),
			caption: String(item.caption || ''),
			credit: String(item.credit || ''),
			attachment_label: String(item.attachment_label || ''),
			preview_url: String(item.preview_url || '')
		};
	}

	function itemLabel(type) {
		if ('image' === type) {
			return label('image', 'Image');
		}

		if ('uploaded_video' === type) {
			return label('uploadedVideo', 'Uploaded Video');
		}

		return label('externalVideo', 'External Video');
	}

	function itemDisplayName(item) {
		if (item.title) {
			return item.title;
		}

		if (('image' === item.type || 'uploaded_video' === item.type) && item.attachment_label) {
			return item.attachment_label;
		}

		return itemLabel(item.type);
	}

	function getItems(manager) {
		return manager.data('items') || [];
	}

	function setItems(manager, items) {
		manager.data('items', items);
	}

	function getCoverId(manager) {
		return String(manager.data('coverId') || '');
	}

	function setCoverId(manager, coverId) {
		manager.data('coverId', coverId || '');
	}

	function serializableItem(item) {
		var data = {
			id: item.id,
			type: item.type,
			title: item.title,
			caption: item.caption,
			credit: item.credit
		};

		if ('external_video' === item.type) {
			data.url = item.url;
		} else {
			data.attachment_id = item.attachment_id;
		}

		return data;
	}

	function syncHiddenFields(manager) {
		var items = getItems(manager);
		var coverId = getCoverId(manager);
		var hasCover = items.some(function (item) {
			return item.id === coverId && 'image' === item.type;
		});

		if (!hasCover) {
			coverId = '';
			setCoverId(manager, '');
		}

		manager.find('[data-art-gallery-media-items-field]').val(JSON.stringify(items.map(serializableItem)));
		manager.find('[data-art-gallery-cover-field]').val(coverId);
		manager.find('[data-art-gallery-empty]').prop('hidden', items.length > 0);
		updateMoveControls(manager);
	}

	function renderPreview(item) {
		if ('image' === item.type && item.preview_url) {
			return '<img class="art-gallery-media-item__thumb" src="' + escapeHtml(item.preview_url) + '" alt="">';
		}

		if ('uploaded_video' === item.type) {
			return '<div class="art-gallery-media-item__file">' + escapeHtml(item.attachment_label || label('uploadedVideoNoSource', 'Uploaded video attachment')) + '</div>';
		}

		return '<div class="art-gallery-media-item__file">' + escapeHtml(item.url || label('externalVideoNoUrl', 'External video URL not set.')) + '</div>';
	}

	function renderItem(manager, item) {
		var isImage = 'image' === item.type;
		var checked = item.id === getCoverId(manager) ? ' checked' : '';
		var attachmentInfo = item.attachment_id ? '<span class="art-gallery-media-item__attachment">' + escapeHtml(label('attachment', 'Attachment')) + ' #' + escapeHtml(item.attachment_id) + '</span>' : '';
		var urlField = '';
		var coverField = '';
		var title = itemDisplayName(item);

		if ('external_video' === item.type) {
			urlField = '<label class="art-gallery-media-item__field"><span>' + escapeHtml(label('url', 'URL')) + '</span><input type="text" class="widefat" data-field="url" value="' + escapeHtml(item.url) + '"></label>';
		}

		if (isImage) {
			coverField = '<label class="art-gallery-media-item__cover"><input type="radio" name="art_gallery_cover_choice" data-art-gallery-cover-choice value="' + escapeHtml(item.id) + '"' + checked + '> ' + escapeHtml(label('setAsCover', 'Set as Cover')) + '</label>';
		}

		return [
			'<div class="art-gallery-media-item" data-art-gallery-media-item data-item-id="' + escapeHtml(item.id) + '">',
			'<div class="art-gallery-media-item__bar">',
			'<span class="art-gallery-media-item__drag-indicator" aria-hidden="true">::</span>',
			'<strong class="art-gallery-media-item__heading">' + escapeHtml(itemLabel(item.type)) + ' — <span data-art-gallery-header-title>' + escapeHtml(title) + '</span></strong>',
			attachmentInfo,
			'<span class="art-gallery-media-item__actions">',
			'<button type="button" class="button-link art-gallery-media-item__move" data-art-gallery-move-up aria-label="' + escapeHtml(label('moveUp', 'Move Up')) + '">↑</button>',
			'<button type="button" class="button-link art-gallery-media-item__move" data-art-gallery-move-down aria-label="' + escapeHtml(label('moveDown', 'Move Down')) + '">↓</button>',
			'<button type="button" class="button-link-delete art-gallery-media-item__remove" data-art-gallery-remove>' + escapeHtml(label('remove', 'Remove')) + '</button>',
			'</span>',
			'</div>',
			'<div class="art-gallery-media-item__body">',
			'<div class="art-gallery-media-item__preview">' + renderPreview(item) + '</div>',
			'<div class="art-gallery-media-item__fields">',
			urlField,
			'<label class="art-gallery-media-item__field"><span>' + escapeHtml(label('title', 'Title')) + '</span><input type="text" class="widefat" data-field="title" value="' + escapeHtml(item.title) + '"></label>',
			'<label class="art-gallery-media-item__field"><span>' + escapeHtml(label('caption', 'Caption')) + '</span><textarea class="widefat" rows="3" data-field="caption">' + escapeHtml(item.caption) + '</textarea></label>',
			'<label class="art-gallery-media-item__field"><span>' + escapeHtml(label('credit', 'Credit')) + '</span><input type="text" class="widefat" data-field="credit" value="' + escapeHtml(item.credit) + '"></label>',
			coverField,
			'</div>',
			'</div>',
			'</div>'
		].join('');
	}

	function render(manager) {
		var list = manager.find('[data-art-gallery-media-items]');
		var html = getItems(manager).map(function (item) {
			return renderItem(manager, item);
		}).join('');

		list.html(html);
		syncHiddenFields(manager);
	}

	function refreshOrderFromDom(manager) {
		var currentItems = getItems(manager);
		var byId = {};
		var ordered = [];

		currentItems.forEach(function (item) {
			byId[item.id] = item;
		});

		manager.find('[data-art-gallery-media-item]').each(function () {
			var item = byId[String($(this).data('itemId'))];

			if (item) {
				ordered.push(item);
			}
		});

		setItems(manager, ordered);
		syncHiddenFields(manager);
	}

	function updateItem(manager, itemId, field, value) {
		var items = getItems(manager);

		items.forEach(function (item) {
			if (item.id === itemId) {
				item[field] = value;
			}
		});

		setItems(manager, items);
		syncHiddenFields(manager);
	}

	function addItem(manager, item) {
		var items = getItems(manager);

		items.push(normalizeItem(item));
		setItems(manager, items);
		render(manager);
	}

	function removeItem(manager, itemId) {
		var items = getItems(manager).filter(function (item) {
			return item.id !== itemId;
		});

		if (getCoverId(manager) === itemId) {
			setCoverId(manager, '');
		}

		setItems(manager, items);
		render(manager);
	}

	function openAttachmentFrame(manager, type) {
		var isImage = 'image' === type;
		var frame = wp.media({
			title: isImage ? label('addImageTitle', 'Select Image') : label('addVideoTitle', 'Select Uploaded Video'),
			button: {
				text: isImage ? label('addImageButton', 'Use this image') : label('addVideoButton', 'Use this video')
			},
			library: {
				type: isImage ? 'image' : 'video'
			},
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			var previewUrl = '';
			var title = attachment.title || attachment.filename || '';

			if (isImage) {
				previewUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
			}

			addItem(manager, {
				id: generateItemId(),
				type: isImage ? 'image' : 'uploaded_video',
				attachment_id: attachment.id,
				title: title,
				caption: attachment.caption || '',
				credit: '',
				attachment_label: title,
				preview_url: previewUrl
			});
		});

		frame.open();
	}

	function initManager(manager) {
		var initialItems = [];

		try {
			initialItems = JSON.parse(manager.attr('data-initial-items') || '[]');
		} catch (error) {
			initialItems = [];
		}

		setItems(manager, initialItems.map(normalizeItem));
		setCoverId(manager, manager.attr('data-initial-cover-id') || '');
		render(manager);

		manager.find('[data-art-gallery-media-items]').sortable({
			handle: '.art-gallery-media-item__bar',
			cancel: 'input, textarea, button, a, [data-art-gallery-remove], [data-art-gallery-move-up], [data-art-gallery-move-down], [data-art-gallery-cover-choice]',
			items: '[data-art-gallery-media-item]',
			placeholder: 'art-gallery-media-item art-gallery-media-item--placeholder',
			forcePlaceholderSize: true,
			tolerance: 'pointer',
			stop: function () {
				refreshOrderFromDom(manager);
			}
		});

		manager.on('click', '[data-art-gallery-add-image]', function (event) {
			event.preventDefault();
			openAttachmentFrame(manager, 'image');
		});

		manager.on('click', '[data-art-gallery-add-video]', function (event) {
			event.preventDefault();
			openAttachmentFrame(manager, 'uploaded_video');
		});

		manager.on('click', '[data-art-gallery-add-external-video]', function (event) {
			event.preventDefault();
			addItem(manager, {
				id: generateItemId(),
				type: 'external_video'
			});
		});

		manager.on('input change', '[data-field]', function () {
			var field = $(this).data('field');
			var itemElement = $(this).closest('[data-art-gallery-media-item]');
			var itemId = String(itemElement.data('itemId'));

			updateItem(manager, itemId, field, $(this).val());

			if ('title' === field) {
				updateHeaderTitle(manager, itemElement, itemId);
			}
		});

		manager.on('change', '[data-art-gallery-cover-choice]', function () {
			setCoverId(manager, String($(this).val()));
			syncHiddenFields(manager);
		});

		manager.on('click', '[data-art-gallery-remove]', function (event) {
			event.preventDefault();

			if (!window.confirm(label('confirmRemove', 'Remove this media item from the Gallery Collection?'))) {
				return;
			}

			removeItem(manager, String($(this).closest('[data-art-gallery-media-item]').data('itemId')));
		});

		manager.on('click', '[data-art-gallery-move-up]', function (event) {
			event.preventDefault();
			moveItem(manager, $(this).closest('[data-art-gallery-media-item]'), -1);
		});

		manager.on('click', '[data-art-gallery-move-down]', function (event) {
			event.preventDefault();
			moveItem(manager, $(this).closest('[data-art-gallery-media-item]'), 1);
		});
	}

	function updateHeaderTitle(manager, itemElement, itemId) {
		var items = getItems(manager);
		var item = items.find(function (currentItem) {
			return currentItem.id === itemId;
		});

		if (!item) {
			return;
		}

		itemElement.find('[data-art-gallery-header-title]').text(itemDisplayName(item));
	}

	function moveItem(manager, itemElement, direction) {
		if (direction < 0) {
			itemElement.prev('[data-art-gallery-media-item]').before(itemElement);
		} else {
			itemElement.next('[data-art-gallery-media-item]').after(itemElement);
		}

		refreshOrderFromDom(manager);
	}

	function updateMoveControls(manager) {
		var itemElements = manager.find('[data-art-gallery-media-item]');

		itemElements.each(function (index) {
			var itemElement = $(this);

			itemElement.find('[data-art-gallery-move-up]').prop('disabled', 0 === index);
			itemElement.find('[data-art-gallery-move-down]').prop('disabled', index === itemElements.length - 1);
		});
	}

	$(function () {
		$('[data-art-gallery-media-manager]').each(function () {
			initManager($(this));
		});
	});
}(jQuery));
