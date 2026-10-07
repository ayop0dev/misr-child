/**
 * Header menu underline (Otour): one bar that sits under the current page's
 * item, slides to the item under the pointer or keyboard focus, and returns
 * when the pointer leaves the menu. A clicked item becomes the current one
 * (in-page links such as "/#catalogue" do not reload the page).
 *
 * Featured items are underlined only while pointed at or current; their
 * star and colour mark them otherwise. Styles in assets/store.css.
 */
(function () {
	'use strict';

	function setup(nav) {
		var menu = nav.querySelector('.eg-header__menu');
		if (!menu) {
			return;
		}

		var bar = document.createElement('span');
		bar.className = 'otour-menu-indicator';
		bar.setAttribute('aria-hidden', 'true');
		nav.appendChild(bar);

		var links = Array.prototype.slice.call(menu.querySelectorAll(':scope > li > a'));

		// The page's own item: WordPress also marks in-page links current.
		var current = links.find(function (link) {
			var item = link.parentElement;
			var marked = item.classList.contains('current-menu-item') || item.classList.contains('current-menu-ancestor');
			return marked && link.getAttribute('href').indexOf('#') === -1;
		}) || null;

		function place(link) {
			if (!link || !nav.offsetWidth) {
				bar.classList.remove('is-shown');
				return;
			}
			var navBox = nav.getBoundingClientRect();
			var box = link.getBoundingClientRect();
			bar.style.setProperty('--otour-indicator-x', (box.left - navBox.left) + 'px');
			bar.style.setProperty('--otour-indicator-width', box.width + 'px');
			bar.classList.add('is-shown');
		}

		place(current);
		// Animate from the next change on, not the first placement.
		requestAnimationFrame(function () {
			bar.classList.add('is-ready');
		});

		links.forEach(function (link) {
			link.addEventListener('mouseenter', function () { place(link); });
			link.addEventListener('focus', function () { place(link); });
			link.addEventListener('click', function () { current = link; place(link); });
		});

		menu.addEventListener('mouseleave', function () { place(current); });
		menu.addEventListener('focusout', function (event) {
			if (!menu.contains(event.relatedTarget)) {
				place(current);
			}
		});

		// Layout changes: window size, the web font arriving.
		var replace = function () {
			bar.classList.remove('is-ready');
			place(current);
			requestAnimationFrame(function () { bar.classList.add('is-ready'); });
		};
		window.addEventListener('resize', replace);
		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(replace);
		}
	}

	function start() {
		document.querySelectorAll('.eg-header__nav').forEach(setup);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})();
