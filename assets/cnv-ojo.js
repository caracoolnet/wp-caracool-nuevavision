/*!
 * Caracool Nueva Visión — Ojo del logo
 * Pinta el ojo en cada contenedor con data-cnv-ojo. La pupila sigue al
 * cursor (al scroll en táctil) y parpadea cada pocos segundos.
 */
(function () {
	'use strict';
	var TRAZO = 'M585.17,75C523.72,32.95,458.39,8.38,389.39,1.69c-76.11-7.11-148.77,8.19-217.77,46.36C105.43,84.92,48.3,137.74,0,206.74c5.39-4.96,10.57-10.13,15.74-15.09,36.87-34.93,75.9-65.76,116.86-92.5,26.74-17.03,54.12-31.48,82.36-43.55,28.88-12.04,58.14-20.16,87.79-24.4-2.48,7.88-3.7,16.23-3.7,25.05,0,22.42,7.98,41.61,23.72,57.35,15.95,15.74,35.14,23.72,57.57,23.72s41.18-7.98,56.92-23.72c16.17-15.74,24.15-34.93,24.15-57.35,0-4.03-.26-7.97-.78-11.81,44.2,13.49,86.67,34.81,127.34,63.99-22.85,9.7-46.14,17.25-70.07,22.42-75.46,17.03-151.57,18.54-228.33,4.53-23.07-4.1-44.2-14.45-63.82-31.26-1.51,17.03,3.45,28.68,14.88,35.36,53.26,31.7,109.32,46.14,168.18,43.34,71.8-3.45,141.23-21.78,208.06-54.98-3.67-23.5-14.23-40.97-31.7-52.83Z';
	var REDUCIDO = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
	var ojos = [];

	function pintar(el) {
		if (el.querySelector('svg.cnv-ojo')) { return; }
		el.insertAdjacentHTML('afterbegin',
			'<svg class="cnv-ojo" viewBox="-6 -6 598 220" role="img" aria-label="Óptica Nueva Visión">' +
			'<g class="cnv-ojo-parpado"><path class="cnv-ojo-trazo" d="' + TRAZO + '"/>' +
			'<circle class="cnv-ojo-pupila" cx="412" cy="59" r="25.5"/></g></svg>');
		var o = { el: el, svg: el.querySelector('svg.cnv-ojo'), visible: false,
			mira: el.getAttribute('data-cnv-ojo-mira') !== 'no', parpadea: el.getAttribute('data-cnv-ojo-parpadea') !== 'no' };
		o.pupila = o.svg.querySelector('.cnv-ojo-pupila');
		ojos.push(o);
		if (window.IntersectionObserver) { new IntersectionObserver(function (e) { o.visible = e[0].isIntersecting; }).observe(el); } else { o.visible = true; }
	}

	function mirar(x, y) {
		ojos.forEach(function (o) {
			if (!o.visible || !o.mira) { return; }
			var r = o.svg.getBoundingClientRect(), p = o.pupila.getBoundingClientRect();
			if (!r.width) { return; }
			var dx = x - (p.left + p.width / 2), dy = y - (p.top + p.height / 2), d = Math.sqrt(dx * dx + dy * dy) || 1;
			var k = Math.min(1, d / 420), max = r.width * 0.05, esc = o.svg.viewBox.baseVal.width / r.width;
			o.pupila.style.transform = 'translate(' + (dx / d * k * max * esc).toFixed(2) + 'px,' + (dy / d * k * max * 0.7 * esc).toFixed(2) + 'px)';
		});
	}

	function parpadeo() {
		setTimeout(function () {
			if (!document.hidden) {
				ojos.forEach(function (o) {
					if (!o.visible || !o.parpadea) { return; }
					o.el.classList.add('cnv-ojo--cierra');
					setTimeout(function () { o.el.classList.remove('cnv-ojo--cierra'); }, 140);
				});
			}
			parpadeo();
		}, 2600 + Math.random() * 3400);
	}

	function arrancar() {
		Array.prototype.forEach.call(document.querySelectorAll('[data-cnv-ojo]'), pintar);
		if (REDUCIDO || !ojos.length) { return; }
		if (window.matchMedia('(hover: hover)').matches) {
			window.addEventListener('pointermove', function (e) { mirar(e.clientX, e.clientY); }, { passive: true });
		} else {
			window.addEventListener('scroll', function () { mirar(window.innerWidth * ((window.scrollY % 900) / 900), window.innerHeight * 0.2); }, { passive: true });
		}
		parpadeo();
	}

	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', arrancar); } else { arrancar(); }
})();
