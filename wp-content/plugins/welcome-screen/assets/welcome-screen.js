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

	function clearSeen(storage, key) {
		if (!storage) {
			return;
		}

		try {
			storage.removeItem(key);
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

		function show() {
			overlay.hidden = false;
			document.body.classList.add('welcome-screen--open');

			if (button) {
				button.focus({ preventScroll: true });
			}
		}

		function dismiss() {
			markSeen(storage, storageKey);
			overlay.hidden = true;
			document.body.classList.remove('welcome-screen--open');
		}

		function reopen() {
			clearSeen(storage, storageKey);
			show();
		}

		if (!hasSeen(storage, storageKey)) {
			show();
		}

		if (button) {
			button.addEventListener('click', dismiss);
		}

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && !overlay.hidden) {
				dismiss();
			}
		});

		document.addEventListener('welcome-screen:open', reopen);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initWelcomeScreen);
	} else {
		initWelcomeScreen();
	}
}());
