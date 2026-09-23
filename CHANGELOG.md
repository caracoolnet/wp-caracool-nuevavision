# Changelog — Caracool Nueva Visión

Numeración: se sube 0.1 en 0.1 cuando una tanda queda cerrada y confirmada; el tercer dígito es para arreglos sobre lo ya publicado.

## 0.3.1 (23 de septiembre de 2026)

- Se retira la cita online: la óptica no la usaba. `/booking/`, `/thank-you-for-booking/` y `/booking-my-account/` pasan a redirigir a `/pedir-cita/`, donde están el teléfono y el WhatsApp.

## 0.3.0 (23 de septiembre de 2026)

Módulo nuevo: **Menú del móvil**.

- En el móvil, el desplegable del widget Menú de Elementor pasa a ser una capa que ocupa la pantalla, con el color Principal del Kit de fondo y dos manchas grandes que se mueven despacio detrás.
- Los enlaces salen grandes (de 30 a 46 px según el ancho), en el color Secundario, y entran uno detrás de otro. Mientras está abierto, la página de detrás no se mueve.
- El logotipo, el botón de la cabecera y la equis para cerrar se quedan por encima de la capa.
- No toca el widget ni el menú de WordPress: quitando el plugin, vuelve el desplegable de siempre. Con «reducir movimiento» se queda quieto.

## 0.2.1 (23 de septiembre de 2026)

- Las páginas de lentes de contacto y gafas de sol pasan a llamarse igual que en la web vieja (`/lentes-de-contacto/` y `/gafas-de-sol/`), así que esas dos direcciones ya no necesitan redirección. En su lugar redirigen los nombres cortos que tuvo la web nueva antes de abrirse.

## 0.2.0 (23 de septiembre de 2026)

Módulo nuevo: **Redirecciones**.

- Las direcciones de la web vieja que Google tiene indexadas (páginas, entradas del blog, la tienda de WooCommerce y la cita online) saltan con un 301 a su página equivalente en la web nueva.
- Solo actúa cuando la dirección no existe (404), así que no puede tapar una página buena. Tabla de direcciones exactas y reglas por patrón para `/product/`, `/product-category/`, `/category/`, `/author/` y los archivos por fecha.
- Pendiente: cuando existan las páginas legales, llevar `/politica-de-proteccion-de-datos/` a la de privacidad.

## 0.1.0 (22 de septiembre de 2026)

Primera versión.

- **Gafas 3D**: sección en los contenedores, **Nueva Visión · Gafas 3D**, con tamaño y posición responsivos, cuánto giran y capa. Imagen fija primero; Three.js y el modelo se cargan cuando el navegador está libre y solo en las páginas que lo usan. Quietas con movimiento reducido o sin WebGL. El modelo y la imagen fija se eligen en los ajustes desde Medios: no van en el repositorio.
- **Ojo del logo**: sección en los contenedores, **Nueva Visión · Ojo del logo**. El plugin pinta el ojo; la pupila sigue al cursor y parpadea. Sustituye al snippet «Ojo del pie» de OneStep y al widget HTML que llevaba el dibujo.
- Página de ajustes en **Caracool → Nueva Visión** con una pestaña por módulo y un interruptor general en cada una. Actualizaciones desde las releases de `caracoolnet/wp-caracool-nuevavision`.
