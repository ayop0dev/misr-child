/**
 * Otour presentation scripts (styles in assets/store.css).
 *
 * - Header menu underline: one bar that sits under the current page's item,
 *   slides to the item under the pointer or keyboard focus, and returns when
 *   the pointer leaves the menu. A clicked item becomes the current one
 *   (in-page links such as "/#catalogue" do not reload the page). Featured
 *   items are underlined only while pointed at or current; their star and
 *   colour mark them otherwise.
 * - Categories band (#catalogue): loops on its own, as in the demo, and
 *   stops while pointed at or focused so a card can be chosen.
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

	// Pixels per millisecond, the demo's speed (about 28 px a second).
	var LOOP_SPEED = 0.028;

	function loop(section) {
		var track = section.querySelector('.eg-category-showcase__track');
		if (!track || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			return;
		}

		var originals = Array.prototype.slice.call(track.children);
		if (originals.length < 2) {
			return;
		}

		// A second set follows the first, so the band never runs out; it is
		// decoration only (hidden from assistive technology and the keyboard).
		originals.forEach(function (item) {
			var copy = item.cloneNode(true);
			copy.setAttribute('aria-hidden', 'true');
			copy.setAttribute('inert', '');
			copy.querySelectorAll('a, button').forEach(function (control) {
				control.setAttribute('tabindex', '-1');
			});
			track.appendChild(copy);
		});

		section.classList.add('is-otour-marquee');
		track.removeAttribute('tabindex');

		// In RTL the copies sit to the left, so the band moves right.
		var direction = getComputedStyle(track).direction === 'rtl' ? 1 : -1;
		var cycle = 0;
		var offset = 0;
		var last = performance.now();
		var paused = false;

		function measure() {
			cycle = Math.abs(track.children[originals.length].offsetLeft - originals[0].offsetLeft);
		}

		function frame(now) {
			if (!paused && cycle > 0) {
				offset = (offset + (now - last) * LOOP_SPEED) % cycle;
				track.style.transform = 'translate3d(' + (direction * offset) + 'px, 0, 0)';
			}
			last = now;
			requestAnimationFrame(frame);
		}

		track.addEventListener('mouseenter', function () { paused = true; });
		track.addEventListener('mouseleave', function () { paused = false; });
		track.addEventListener('focusin', function () { paused = true; });
		track.addEventListener('focusout', function () { paused = false; });

		measure();
		if (typeof window.ResizeObserver === 'function') {
			new ResizeObserver(measure).observe(track);
		} else {
			window.addEventListener('resize', measure);
		}
		requestAnimationFrame(frame);
	}

	function start() {
		document.querySelectorAll('.eg-header__nav').forEach(setup);
		var catalogue = document.getElementById('catalogue');
		if (catalogue && catalogue.matches('.eg-category-showcase')) {
			loop(catalogue);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})();
