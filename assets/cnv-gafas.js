/*!
 * Caracool Nueva Visión — Gafas 3D (cargador)
 * Mete la capa con la imagen fija y, cuando el navegador está libre, carga
 * el módulo 3D y el modelo. Con movimiento reducido o sin WebGL, se queda la
 * imagen. El giro sigue al scroll del contenedor.
 */
(function () {
	'use strict';
	var CFG = window.CNV_GAFAS;
	if (!CFG) { return; }
	var REDUCIDO = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

	function hayWebGL() {
		try { var c = document.createElement('canvas'); return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl'))); } catch (e) { return false; }
	}

	function capa(el) {
		var z = document.createElement('div');
		z.className = 'cnv-gafas';
		z.setAttribute('aria-hidden', 'true');
		var img = document.createElement('img');
		img.src = CFG.poster; img.alt = ''; img.width = 1800; img.height = 1260; img.decoding = 'async';
		var lienzo = document.createElement('canvas');
		z.appendChild(img); z.appendChild(lienzo);
		// detrás de todo el contenido del contenedor (el fondo vivo, si lo hay, se pone delante al llegar)
		el.insertBefore(z, el.firstChild);
		return { zona: z, lienzo: lienzo };
	}

	function iniciar(el, c, mod, modelo) {
		var g = mod.gafas3d({
			lienzo: c.lienzo, zona: c.zona, glb: modelo.slice(0), reducido: REDUCIDO,
			giro: parseFloat(el.getAttribute('data-cnv-gafas-giro')) || 0,
			alListo: function () { requestAnimationFrame(function () { c.zona.classList.add('cnv-gafas--lista'); }); }
		});
		function scroll() {
			var r = el.getBoundingClientRect();
			g.progreso(-r.top / Math.max(1, r.height * 0.9));
		}
		window.addEventListener('scroll', scroll, { passive: true });
		window.addEventListener('resize', scroll);
		scroll();
	}

	function arrancar() {
		var els = document.querySelectorAll('[data-cnv-gafas]');
		if (!els.length) { return; }
		var capas = Array.prototype.map.call(els, capa);
		if (REDUCIDO || !hayWebGL()) { return; }
		var cargar = function () {
			Promise.all([
				import(CFG.modulo),
				fetch(CFG.modelo).then(function (r) { if (!r.ok) { throw new Error('modelo'); } return r.arrayBuffer(); })
			]).then(function (res) {
				Array.prototype.forEach.call(els, function (el, i) { iniciar(el, capas[i], res[0], res[1]); });
			}).catch(function (e) { if (window.console) { console.warn('Nueva Visión · gafas 3D: se queda la imagen fija', e); } });
		};
		var libre = function () { (window.requestIdleCallback || function (f) { setTimeout(f, 300); })(cargar, { timeout: 2500 }); };
		if (document.readyState === 'complete') { libre(); } else { window.addEventListener('load', libre); }
	}

	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', arrancar); } else { arrancar(); }
})();
