<?php
/**
 * Caracool Nueva Visión — Módulo Ojo del logo
 * ─────────────────────────────────────────────────────────────────────
 * Pinta el ojo del logotipo dentro de un contenedor. La pupila sigue al
 * cursor (al scroll en táctil) y el ojo parpadea cada pocos segundos.
 *
 * CÓMO SE USA
 *  En Elementor, en un contenedor vacío con el ancho que se quiera para el
 *  ojo: Estilo → Nueva Visión · Ojo del logo. Color del trazo y del brillo
 *  con el selector nativo (admite globales del Kit). No hace falta ningún
 *  widget HTML: el dibujo lo pone el plugin.
 *
 *  Solo carga cnv-ojo.css y cnv-ojo.js (≈2 KB) en las páginas que lo usan.
 *  Se para fuera de pantalla y con la pestaña oculta; quieto con
 *  movimiento reducido.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CNV_Ojo {

	const OPTION_KEY = 'caracool_nuevavision_ojo';

	private static $visto = false;

	public function __construct() {
		add_action( 'elementor/element/container/section_border/after_section_end', array( $this, 'controles' ), 31, 2 );
		add_action( 'elementor/frontend/before_render', array( $this, 'atributos' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'registrar' ) );
		add_action( 'wp_footer', array( $this, 'imprimir' ), 5 );
		add_action( 'caracool_nuevavision_settings_panels', array( $this, 'panel' ) );
		add_action( 'admin_post_caracool_nuevavision_save', array( $this, 'guardar' ), 5 );
	}

	public function controles( $element, $args ) {
		if ( ! class_exists( '\Elementor\Controls_Manager' ) ) {
			return;
		}
		$M = '\Elementor\Controls_Manager';

		$element->start_controls_section( 'cnv_ojo_seccion', array(
			'label' => 'Nueva Visión · Ojo del logo',
			'tab'   => $M::TAB_STYLE,
		) );

		$element->add_control( 'cnv_ojo', array(
			'label'        => 'Ojo del logo',
			'type'         => $M::SWITCHER,
			'label_on'     => 'Sí',
			'label_off'    => 'No',
			'return_value' => 'si',
			'default'      => '',
			'description'  => 'Pinta el ojo del logotipo en este contenedor, a todo su ancho. Se ve en la web, no en el editor.',
		) );

		$element->add_control( 'cnv_ojo_color', array(
			'label'     => 'Color del trazo',
			'type'      => $M::COLOR,
			'global'    => array( 'active' => true ),
			'selectors' => array( '{{WRAPPER}}' => '--cnv-ojo-color: {{VALUE}};' ),
			'condition' => array( 'cnv_ojo' => 'si' ),
		) );

		$element->add_control( 'cnv_ojo_brillo', array(
			'label'     => 'Color del brillo',
			'type'      => $M::COLOR,
			'global'    => array( 'active' => true ),
			'selectors' => array( '{{WRAPPER}}' => '--cnv-ojo-brillo: {{VALUE}};' ),
			'condition' => array( 'cnv_ojo' => 'si' ),
		) );

		$element->add_control( 'cnv_ojo_mira', array(
			'label'        => 'Mira al cursor',
			'type'         => $M::SWITCHER,
			'label_on'     => 'Sí',
			'label_off'    => 'No',
			'return_value' => 'si',
			'default'      => 'si',
			'condition'    => array( 'cnv_ojo' => 'si' ),
		) );

		$element->add_control( 'cnv_ojo_parpadea', array(
			'label'        => 'Parpadea',
			'type'         => $M::SWITCHER,
			'label_on'     => 'Sí',
			'label_off'    => 'No',
			'return_value' => 'si',
			'default'      => 'si',
			'condition'    => array( 'cnv_ojo' => 'si' ),
		) );

		$element->end_controls_section();
	}

	public function atributos( $element ) {
		if ( ! is_object( $element ) || ! method_exists( $element, 'get_type' ) || 'container' !== $element->get_type() ) {
			return;
		}
		$a = $element->get_settings_for_display();
		if ( empty( $a['cnv_ojo'] ) || 'si' !== $a['cnv_ojo'] ) {
			return;
		}
		$element->add_render_attribute( '_wrapper', 'data-cnv-ojo', 'si' );
		$element->add_render_attribute( '_wrapper', 'data-cnv-ojo-mira', ( isset( $a['cnv_ojo_mira'] ) && 'si' !== $a['cnv_ojo_mira'] ) ? 'no' : 'si' );
		$element->add_render_attribute( '_wrapper', 'data-cnv-ojo-parpadea', ( isset( $a['cnv_ojo_parpadea'] ) && 'si' !== $a['cnv_ojo_parpadea'] ) ? 'no' : 'si' );
		self::$visto = true;
	}

	public function registrar() {
		$base = CARACOOL_NUEVAVISION_URL . 'assets/';
		wp_register_style( 'cnv-ojo', $base . 'cnv-ojo.css', array(), cnv_ver( 'cnv-ojo.css' ) );
		wp_register_script( 'cnv-ojo', $base . 'cnv-ojo.js', array(), cnv_ver( 'cnv-ojo.js' ), true );
	}

	private static function pagina_lo_usa() {
		if ( self::$visto ) {
			return true;
		}
		foreach ( cnv_documentos_de_la_pagina() as $id ) {
			$datos = get_post_meta( $id, '_elementor_data', true );
			if ( is_string( $datos ) && false !== strpos( $datos, '"cnv_ojo":"si"' ) ) {
				return true;
			}
		}
		return false;
	}

	public function imprimir() {
		if ( cnv_en_editor() ) {
			return;
		}
		$c = self::ajustes();
		if ( 'si' !== $c['activo'] || ! self::pagina_lo_usa() ) {
			return;
		}
		wp_enqueue_style( 'cnv-ojo' );
		wp_enqueue_script( 'cnv-ojo' );
	}

	public static function ajustes() {
		$g = get_option( self::OPTION_KEY, array() );
		return array( 'activo' => ( isset( $g['activo'] ) && 'no' === $g['activo'] ) ? 'no' : 'si' );
	}

	public function panel() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$c = self::ajustes();
		?>
		<section id="cnv-ojo" class="cm-modulo" data-titulo="Ojo del logo">
			<div class="cm-card">
				<div class="cm-card-head">
					<div class="cm-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="2.6"/></svg></div>
					<h2>Ojo del logo</h2>
				</div>
				<p class="cm-card-desc">El ojo del logotipo dentro de un contenedor: la pupila sigue al cursor y parpadea de vez en cuando. Se activa en Elementor, en un contenedor vacío: <strong>Estilo → Nueva Visión · Ojo del logo</strong>. Colores con el selector nativo; sin elegir, el trazo va en el Secundario del Kit y el brillo en el de Acento.</p>
				<div class="cm-campo">
					<label for="cnv_ojo_activo">Permitir el ojo</label>
					<div>
						<label class="cm-sw"><input type="checkbox" name="cnv_ojo[activo]" id="cnv_ojo_activo" value="si" <?php checked( 'si', $c['activo'] ); ?>><span></span></label>
						<span class="cm-hint">Apagado, no se pinta en ninguna página.</span>
					</div>
				</div>
			</div>
		</section>
		<?php
	}

	public function guardar() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		check_admin_referer( 'caracool_nuevavision_save' );
		$p = ( isset( $_POST['cnv_ojo'] ) && is_array( $_POST['cnv_ojo'] ) ) ? wp_unslash( $_POST['cnv_ojo'] ) : array();
		update_option( self::OPTION_KEY, array( 'activo' => ( isset( $p['activo'] ) && 'si' === $p['activo'] ) ? 'si' : 'no' ) );
	}
}

new CNV_Ojo();
