<?php
/**
 * Caracool Nueva Visión — Módulo Menú del móvil
 * ─────────────────────────────────────────────────────────────────────
 * En el móvil, el desplegable del widget Menú de Elementor pasa a ser una
 * capa a pantalla completa, con el color Principal del Kit de fondo y dos
 * manchas grandes que se mueven despacio detrás. Los enlaces salen grandes,
 * en el color Secundario, y entran uno detrás de otro.
 *
 * POR QUÉ AQUÍ Y NO EN MOTION
 *  Es una decisión de diseño de esta web. Motion lo usan otros clientes y
 *  no tiene por qué cargar con lo que se pide en una sola óptica.
 *
 * QUÉ NO HACE
 *  No toca el widget de Elementor ni el menú de WordPress. Si se quita el
 *  plugin, el menú vuelve a ser el desplegable de siempre.
 *
 * Los dos archivos suman poco más de 4 KB y se cargan en toda la web,
 * porque la cabecera está en todas las páginas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Caracool_NuevaVision_Menu {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
	}

	public function assets() {
		if ( cnv_en_editor() ) {
			return;
		}
		$base = CARACOOL_NUEVAVISION_URL . 'assets/';
		wp_enqueue_style( 'cnv-menu', $base . 'cnv-menu.css', array(), cnv_ver( 'cnv-menu.css' ) );
		wp_enqueue_script( 'cnv-menu', $base . 'cnv-menu.js', array(), cnv_ver( 'cnv-menu.js' ), true );
	}
}

new Caracool_NuevaVision_Menu();
