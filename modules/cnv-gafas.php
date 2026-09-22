<?php
/**
 * Caracool Nueva Visión — Módulo Gafas 3D
 * ─────────────────────────────────────────────────────────────────────
 * Unas gafas en 3D (Three.js) que se quedan quietas en pantalla y giran
 * con el scroll mientras el contenedor sube y las va tapando. Con el ratón
 * se inclinan un poco.
 *
 * CÓMO SE USA
 *  En Elementor, en el contenedor (normalmente el hero): Estilo → Nueva
 *  Visión · Gafas 3D. Tamaño y posición son responsivos; también cuánto
 *  giran y si van detrás o delante del texto.
 *
 * CÓMO CARGA
 *  1. Primero entra una imagen fija de la misma pose (≈20 KB).
 *  2. Cuando la página ha cargado y el navegador está libre, se piden
 *     Three.js (solo las piezas que se usan, ≈145 KB comprimido) y el
 *     modelo (≈160 KB), y el 3D sustituye a la imagen sin salto.
 *  3. Solo se pinta mientras está en pantalla. Con movimiento reducido se
 *     queda la imagen fija.
 *  El código sale del plugin; el modelo (.glb) y la imagen fija se suben a la
 *  biblioteca de medios y se eligen en Caracool → Nueva Visión → Gafas 3D.
 *  Así el repositorio público no lleva el modelo, que es un recurso con
 *  licencia de la web. Sin modelo elegido, el módulo no carga nada.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CNV_Gafas {

	const OPTION_KEY = 'caracool_nuevavision_gafas';

	private static $visto = false;

	public function __construct() {
		add_action( 'elementor/element/container/section_border/after_section_end', array( $this, 'controles' ), 30, 2 );
		add_action( 'elementor/frontend/before_render', array( $this, 'atributos' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'registrar' ) );
		add_action( 'wp_footer', array( $this, 'imprimir' ), 5 );
		add_action( 'caracool_nuevavision_settings_panels', array( $this, 'panel' ) );
		add_action( 'admin_post_caracool_nuevavision_save', array( $this, 'guardar' ), 5 );
		add_filter( 'upload_mimes', array( $this, 'permitir_glb' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'tipo_glb' ), 10, 4 );
	}

	/** Deja subir modelos .glb a la biblioteca, solo a administradores. */
	public function permitir_glb( $mimes ) {
		if ( current_user_can( 'manage_options' ) ) {
			$mimes['glb'] = 'model/gltf-binary';
		}
		return $mimes;
	}

	public function tipo_glb( $datos, $archivo, $nombre, $mimes ) {
		if ( current_user_can( 'manage_options' ) && preg_match( '/\.glb$/i', (string) $nombre ) ) {
			$datos['ext']             = 'glb';
			$datos['type']            = 'model/gltf-binary';
			$datos['proper_filename'] = $nombre;
		}
		return $datos;
	}

	// ── Controles en Elementor ──────────────────────────────────────────

	public function controles( $element, $args ) {
		if ( ! class_exists( '\Elementor\Controls_Manager' ) ) {
			return;
		}
		$M = '\Elementor\Controls_Manager';

		$element->start_controls_section( 'cnv_gafas_seccion', array(
			'label' => 'Nueva Visión · Gafas 3D',
			'tab'   => $M::TAB_STYLE,
		) );

		$element->add_control( 'cnv_gafas', array(
			'label'        => 'Gafas 3D',
			'type'         => $M::SWITCHER,
			'label_on'     => 'Sí',
			'label_off'    => 'No',
			'return_value' => 'si',
			'default'      => '',
			'description'  => 'Las gafas se quedan quietas y giran con el scroll mientras este contenedor sube. Solo se ven en la web, no en el editor.',
		) );

		$element->add_responsive_control( 'cnv_gafas_ancho', array(
			'label'           => 'Tamaño (% del ancho de pantalla)',
			'type'            => $M::SLIDER,
			'size_units'      => array( 'vw' ),
			'range'           => array( 'vw' => array( 'min' => 20, 'max' => 200 ) ),
			'default'         => array( 'unit' => 'vw', 'size' => 104 ),
			'tablet_default'  => array( 'unit' => 'vw', 'size' => 130 ),
			'mobile_default'  => array( 'unit' => 'vw', 'size' => 175 ),
			'selectors'       => array( '{{WRAPPER}}' => '--cnv-gafas-ancho: {{SIZE}}vw;' ),
			'condition'       => array( 'cnv_gafas' => 'si' ),
		) );

		$element->add_responsive_control( 'cnv_gafas_x', array(
			'label'          => 'Desde la derecha (% del ancho)',
			'type'           => $M::SLIDER,
			'size_units'     => array( 'vw' ),
			'range'          => array( 'vw' => array( 'min' => -100, 'max' => 100 ) ),
			'default'        => array( 'unit' => 'vw', 'size' => -12 ),
			'tablet_default' => array( 'unit' => 'vw', 'size' => -15 ),
			'mobile_default' => array( 'unit' => 'vw', 'size' => -37.5 ),
			'selectors'      => array( '{{WRAPPER}}' => '--cnv-gafas-x: {{SIZE}}vw;' ),
			'condition'      => array( 'cnv_gafas' => 'si' ),
		) );

		$element->add_responsive_control( 'cnv_gafas_y', array(
			'label'          => 'Desde arriba (% del alto)',
			'type'           => $M::SLIDER,
			'size_units'     => array( 'vh' ),
			'range'          => array( 'vh' => array( 'min' => -60, 'max' => 80 ) ),
			'default'        => array( 'unit' => 'vh', 'size' => -16 ),
			'tablet_default' => array( 'unit' => 'vh', 'size' => -4 ),
			'mobile_default' => array( 'unit' => 'vh', 'size' => 2 ),
			'selectors'      => array( '{{WRAPPER}}' => '--cnv-gafas-y: {{SIZE}}vh;' ),
			'condition'      => array( 'cnv_gafas' => 'si' ),
		) );

		$element->add_control( 'cnv_gafas_giro', array(
			'label'       => 'Cuánto giran con el scroll',
			'type'        => $M::NUMBER,
			'min'         => 0,
			'max'         => 2,
			'step'        => 0.1,
			'default'     => 1,
			'description' => '1 es de tres cuartos a perfil. 0 no giran.',
			'condition'   => array( 'cnv_gafas' => 'si' ),
		) );

		$element->add_control( 'cnv_gafas_capa', array(
			'label'     => 'Capa',
			'type'      => $M::SELECT,
			'options'   => array( 'detras' => 'Detrás del texto', 'delante' => 'Delante del texto' ),
			'default'   => 'detras',
			'condition' => array( 'cnv_gafas' => 'si' ),
		) );

		$element->end_controls_section();
	}

	public function atributos( $element ) {
		if ( ! is_object( $element ) || ! method_exists( $element, 'get_type' ) || 'container' !== $element->get_type() ) {
			return;
		}
		$a = $element->get_settings_for_display();
		if ( empty( $a['cnv_gafas'] ) || 'si' !== $a['cnv_gafas'] ) {
			return;
		}
		$giro = isset( $a['cnv_gafas_giro'] ) && is_numeric( $a['cnv_gafas_giro'] ) ? max( 0, min( 2, (float) $a['cnv_gafas_giro'] ) ) : 1;
		$capa = ( isset( $a['cnv_gafas_capa'] ) && 'delante' === $a['cnv_gafas_capa'] ) ? 'delante' : 'detras';
		$element->add_render_attribute( '_wrapper', 'data-cnv-gafas', 'si' );
		$element->add_render_attribute( '_wrapper', 'data-cnv-gafas-giro', (string) $giro );
		$element->add_render_attribute( '_wrapper', 'data-cnv-gafas-capa', $capa );
		self::$visto = true;
	}

	// ── Recursos, solo donde se usan ────────────────────────────────────

	public function registrar() {
		$base = CARACOOL_NUEVAVISION_URL . 'assets/';
		wp_register_style( 'cnv-gafas', $base . 'cnv-gafas.css', array(), cnv_ver( 'cnv-gafas.css' ) );
		wp_register_script( 'cnv-gafas', $base . 'cnv-gafas.js', array(), cnv_ver( 'cnv-gafas.js' ), true );
	}

	private static function pagina_lo_usa() {
		if ( self::$visto ) {
			return true;
		}
		foreach ( cnv_documentos_de_la_pagina() as $id ) {
			$datos = get_post_meta( $id, '_elementor_data', true );
			if ( is_string( $datos ) && false !== strpos( $datos, '"cnv_gafas":"si"' ) ) {
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
		if ( 'si' !== $c['activo'] || '' === $c['modelo'] || '' === $c['poster'] || ! self::pagina_lo_usa() ) {
			return;
		}
		wp_enqueue_style( 'cnv-gafas' );
		wp_enqueue_script( 'cnv-gafas' );
		wp_add_inline_script( 'cnv-gafas', 'window.CNV_GAFAS=' . wp_json_encode( array(
			'poster' => $c['poster'],
			'modelo' => $c['modelo'],
			'modulo' => CARACOOL_NUEVAVISION_URL . 'assets/gafas/gafas-3d.js?v=' . rawurlencode( cnv_ver( 'gafas/gafas-3d.js' ) ),
		) ) . ';', 'before' );
	}

	// ── Ajustes ─────────────────────────────────────────────────────────

	public static function ajustes() {
		$g = get_option( self::OPTION_KEY, array() );
		return array(
			'activo' => ( isset( $g['activo'] ) && 'no' === $g['activo'] ) ? 'no' : 'si',
			'modelo' => isset( $g['modelo'] ) ? esc_url_raw( $g['modelo'] ) : '',
			'poster' => isset( $g['poster'] ) ? esc_url_raw( $g['poster'] ) : '',
		);
	}

	public function panel() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$c = self::ajustes();
		?>
		<section id="cnv-gafas" class="cm-modulo" data-titulo="Gafas 3D">
			<div class="cm-card">
				<div class="cm-card-head">
					<div class="cm-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="6.5" cy="14" r="3.5"/><circle cx="17.5" cy="14" r="3.5"/><path d="M10 14c1.2-1 2.8-1 4 0M3 13l2-6M21 13l-2-6"/></svg></div>
					<h2>Gafas 3D</h2>
				</div>
				<p class="cm-card-desc">Unas gafas en 3D que se quedan quietas en pantalla y giran con el scroll mientras el contenedor sube. Se activan en Elementor, en el contenedor: <strong>Estilo → Nueva Visión · Gafas 3D</strong>. Primero se ve una imagen fija; el 3D (≈300 KB en total) se carga cuando la página ya está lista y solo en las páginas que lo usan.</p>
				<div class="cm-campo">
					<label for="cnv_gafas_activo">Permitir las gafas 3D</label>
					<div>
						<label class="cm-sw"><input type="checkbox" name="cnv_gafas[activo]" id="cnv_gafas_activo" value="si" <?php checked( 'si', $c['activo'] ); ?>><span></span></label>
						<span class="cm-hint">Apagado, ninguna página las carga aunque estén elegidas en Elementor.</span>
					</div>
				</div>
				<div class="cm-campo">
					<label for="cnv_gafas_modelo">Modelo 3D (.glb)</label>
					<div>
						<input type="text" name="cnv_gafas[modelo]" id="cnv_gafas_modelo" value="<?php echo esc_attr( $c['modelo'] ); ?>" style="width:100%;max-width:560px" placeholder="https://…/gafas.glb">
						<span class="cm-hint">Súbelo a Medios y pega aquí su URL. El plugin deja subir archivos .glb a los administradores. Sin modelo, el módulo no carga nada.</span>
					</div>
				</div>
				<div class="cm-campo">
					<label for="cnv_gafas_poster">Imagen fija</label>
					<div>
						<input type="text" name="cnv_gafas[poster]" id="cnv_gafas_poster" value="<?php echo esc_attr( $c['poster'] ); ?>" style="width:100%;max-width:560px" placeholder="https://…/gafas-poster.webp">
						<span class="cm-hint">La misma pose del modelo, 1800 × 1260 px con fondo transparente. Es lo que se ve mientras carga el 3D y con movimiento reducido.</span>
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
		$p = ( isset( $_POST['cnv_gafas'] ) && is_array( $_POST['cnv_gafas'] ) ) ? wp_unslash( $_POST['cnv_gafas'] ) : array();
		update_option( self::OPTION_KEY, array(
			'activo' => ( isset( $p['activo'] ) && 'si' === $p['activo'] ) ? 'si' : 'no',
			'modelo' => isset( $p['modelo'] ) ? esc_url_raw( trim( $p['modelo'] ) ) : '',
			'poster' => isset( $p['poster'] ) ? esc_url_raw( trim( $p['poster'] ) ) : '',
		) );
	}
}

new CNV_Gafas();
