/*!
 * Caracool Nueva Visión — Menú del móvil a pantalla completa
 * ─────────────────────────────────────────────────────────────────────
 * No cambia el widget Menú de Elementor ni el menú de WordPress: solo
 * marca la cabecera para que el CSS convierta el desplegable en una capa
 * a pantalla completa, numera los enlaces para que entren escalonados y
 * bloquea el scroll de la página mientras está abierto.
 */
(function () {
	'use strict';

	function cabecera() {
		return document.querySelector('[data-elementor-type="header"]')
			|| document.querySelector('header.elementor-location-header');
	}

	function arrancar() {
		var cab = cabecera();
		if (!cab || cab.dataset.cnvMenu === '1') { return; }
		if (!cab.querySelector('.elementor-nav-menu--dropdown')) { return; }
		cab.dataset.cnvMenu = '1';
		cab.classList.add('cnv-menu-pantalla');

		function alto() {
			cab.style.setProperty('--cnv-cabecera-alto', Math.round(cab.getBoundingClientRect().height) + 'px');
		}
		alto();

		var enlaces = cab.querySelectorAll('.elementor-nav-menu--dropdown .elementor-item');
		Array.prototype.forEach.call(enlaces, function (a, i) { a.style.setProperty('--cnv-i', i); });

		function estado() {
			var abierto = !!cab.querySelector('.elementor-menu-toggle.elementor-active');
			document.documentElement.classList.toggle('cnv-menu-abierto', abierto);
		}

		cab.addEventListener('click', function () { window.setTimeout(estado, 30); });
		document.addEventListener('keyup', function (e) { if (e.key === 'Escape') { window.setTimeout(estado, 30); } });
		window.addEventListener('resize', alto);
		estado();
	}

	function listo() { window.setTimeout(arrancar, 80); }

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', listo);
	} else {
		listo();
	}
})();
