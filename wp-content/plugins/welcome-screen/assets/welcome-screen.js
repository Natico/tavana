(function () {
	function getStorage(frequency) {
		try {
			return frequency === 'session' ? window.sessionStorage : window.localStorage;
		} catch (error) {
			return null;
		}
	}

	function markSeen(storage, key) {
		if (!storage) {
			return;
		}

		try {
			storage.setItem(key, '1');
		} catch (error) {
			return;
		}
	}

	function hasSeen(storage, key) {
		if (!storage) {
			return false;
		}

		try {
			return storage.getItem(key) === '1';
		} catch (error) {
			return false;
		}
	}

	function initWelcomeScreen() {
		var overlay = document.querySelector('[data-welcome-screen]');

		if (!overlay) {
			return;
		}

		var button = overlay.querySelector('[data-welcome-screen-enter]');
		var frequency = overlay.getAttribute('data-welcome-screen-frequency') || 'once';
		var storageKey = overlay.getAttribute('data-welcome-screen-storage-key') || 'welcomeScreen';
		var storage = getStorage(frequency);

		if (hasSeen(storage, storageKey)) {
			overlay.remove();
			return;
		}

		function dismiss() {
			markSeen(storage, storageKey);
			overlay.hidden = true;
			document.body.classList.remove('welcome-screen--open');
		}

		overlay.hidden = false;
		document.body.classList.add('welcome-screen--open');

		if (button) {
			button.addEventListener('click', dismiss);
			button.focus({ preventScroll: true });
		}

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && !overlay.hidden) {
				dismiss();
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initWelcomeScreen);
	} else {
		initWelcomeScreen();
	}
}());
