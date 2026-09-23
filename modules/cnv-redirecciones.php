<?php
/**
 * Caracool Nueva Visión — Módulo Redirecciones
 * ─────────────────────────────────────────────────────────────────────
 * Las direcciones de la web vieja (WooCommerce, blog, cita online) que
 * Google tiene indexadas, llevadas a su página equivalente en la web nueva
 * con un 301. Es propio de esta web, así que vive aquí y no en Motion.
 *
 * CÓMO FUNCIONA
 *  Solo actúa cuando WordPress ya ha decidido que la dirección no existe
 *  (404). Primero mira la tabla de direcciones exactas y, si no está, las
 *  reglas por patrón (productos, categorías de la tienda, autores…).
 *  Nunca toca una dirección que sí existe, así que no puede tapar una
 *  página buena.
 *
 * LAS QUE NO SE TOCAN
 *  /gafas-graduadas/ y /contacto/ existen igual en la web nueva.
 *
 * CUANDO HAYA PÁGINAS LEGALES
 *  Cambiar el destino de /politica-de-proteccion-de-datos/ a la nueva
 *  página de privacidad.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Caracool_NuevaVision_Redirecciones {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'redirigir' ), 1 );
	}

	/** Dirección vieja (sin barras al principio ni al final) => destino. */
	public static function tabla() {
		return array(
			// Páginas de la web vieja.
			'nosotros'                        => '/nosotras/',
			'el-centro'                       => '/nosotras/',
			'testimonial-list'                => '/nosotras/',
			// Estas dos páginas se llaman igual que en la web vieja, así que
			// no hacen falta. Lo que sí redirige es el nombre corto que llegó
			// a tener la web nueva antes de abrirla.
			'lentes-contacto'                 => '/lentes-de-contacto/',
			'gafas-sol'                       => '/gafas-de-sol/',
			'shop'                            => '/gafas/',
			'tienda'                          => '/gafas/',
			'financiacion'                    => '/contacto/',
			'preguntas-frecuentes'            => '/contacto/',
			'cart-2'                          => '/gafas/',
			'checkout-2'                      => '/gafas/',
			'cart'                            => '/gafas/',
			'checkout'                        => '/gafas/',
			'my-account'                      => '/pedir-cita/',
			'mi-cuenta'                       => '/pedir-cita/',
			'thank-you-for-booking-2'         => '/pedir-cita/',
			// La cita online se retiró: la óptica no la usaba. Todo lo que
			// llevaba a ella va a «Pedir cita», donde están el teléfono y el
			// WhatsApp.
			'booking'                         => '/pedir-cita/',
			'thank-you-for-booking'           => '/pedir-cita/',
			'booking-my-account'              => '/pedir-cita/',
			'inicio-nueva-version-accesible'  => '/',
			'blog'                            => '/',
			'politica-de-proteccion-de-datos' => '/',
			// Entradas del blog con tema claro.
			'cuidado-con-la-luz-azul'                                             => '/salud-visual/',
			'como-puedes-evitar-el-aumento-de-la-miopia-de-una-forma-divertida'   => '/control-miopia/',
			'consejos-de-como-limpiar-tus-gafas'                                  => '/gafas/',
			'quien-dice-que-los-dias-nublados-no-hay-que-llevar-gafas-de-sol'     => '/gafas-de-sol/',
			'que-bonito-se-ve-el-bando-con-estas-espectaculares-gafas-de-sol'     => '/gafas-de-sol/',
			'la-marca-del-disenador-tom-ford-ya-disponible-en-optica-nueva-vision' => '/gafas-de-sol/',
			// Entradas sueltas, sin equivalente: a la portada.
			'te-parece-curioso'                                => '/',
			'quereis-conocer-a-la-ganadora-de-la-gafa-de-sol'  => '/',
			'entrega-del-cheque-de-5000-euros'                 => '/',
			'beneficios-del-pepino'                            => '/',
			'nos-visita-telearchena'                           => '/',
		);
	}

	/** Reglas por patrón, para lo que era la tienda y los archivos del blog. */
	public static function patrones() {
		return array(
			'#^product/#'          => '/gafas/',
			'#^producto/#'         => '/gafas/',
			'#^product-category/#' => '/gafas/',
			'#^product-tag/#'      => '/gafas/',
			'#^categoria-producto/#' => '/gafas/',
			'#^category/#'         => '/',
			'#^tag/#'              => '/',
			'#^author/#'           => '/',
			'#^\d{4}/\d{2}/#'      => '/',
		);
	}

	public function redirigir() {
		if ( ! is_404() || is_admin() ) {
			return;
		}

		$ruta = wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH );
		$ruta = trim( (string) $ruta, '/' );
		if ( '' === $ruta ) {
			return;
		}
		// El feed de una entrada vieja va a donde va la entrada.
		$ruta = preg_replace( '#/feed$#', '', $ruta );

		$tabla = self::tabla();
		if ( isset( $tabla[ $ruta ] ) ) {
			$this->saltar( $tabla[ $ruta ] );
		}

		foreach ( self::patrones() as $patron => $destino ) {
			if ( preg_match( $patron, $ruta . '/' ) ) {
				$this->saltar( $destino );
			}
		}
	}

	private function saltar( $destino ) {
		wp_safe_redirect( home_url( $destino ), 301, 'Caracool Nueva Visión' );
		exit;
	}
}

new Caracool_NuevaVision_Redirecciones();
