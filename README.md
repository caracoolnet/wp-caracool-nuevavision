# Caracool Nueva Visión

Plugin de WordPress con las piezas propias de la web de **Óptica Nueva Visión** (Archena). Lo que solo tiene sentido en esta web vive aquí y no en Caracool Motion, que usan todas.

**Versión actual:** 0.1.0 · **Requiere:** WordPress 6.0+, Elementor 3.16+ (contenedores flexbox)

Mismo chasis que el resto de plugins de Caracool: archivo principal delgado, módulos autorregistrados en `modules/`, una pestaña por módulo en **Caracool → Nueva Visión**, el menú compartido `inc/caracool-menu.php` (igual byte a byte que en `caracoolnet/wp-caracool-shared`) y actualizaciones desde las releases de este repositorio.

## Módulos

### Gafas 3D

Unas gafas en 3D que se quedan quietas en pantalla y giran con el scroll mientras el contenedor sube y las va tapando. Con el ratón se inclinan un poco.

Se activan en el contenedor, normalmente el hero: **Estilo → Nueva Visión · Gafas 3D**.

| Opción | Qué hace |
|---|---|
| Tamaño | Ancho de las gafas en % de la pantalla. Responsivo (escritorio 104, tableta 130, móvil 175). |
| Desde la derecha / desde arriba | Posición de la capa. Responsivo. |
| Cuánto giran | 1 es de tres cuartos a perfil; 0, quietas. |
| Capa | Detrás o delante del texto. |

- Primero se ve una imagen fija con la misma pose (≈20 KB). Cuando la página ha cargado y el navegador está libre, se piden Three.js (solo las piezas usadas, ≈145 KB comprimido) y el modelo (≈160 KB), y el 3D sustituye a la imagen sin salto.
- Solo se pinta mientras está en pantalla. Con movimiento reducido o sin WebGL, se queda la imagen fija.
- La capa va fija en pantalla y el contenedor la recorta con `clip-path`; convive con el fondo vivo de Caracool Motion.
- El código sale del plugin y solo en las páginas que lo usan. **El modelo (.glb) y la imagen fija no van en el repositorio**: son recursos con licencia de la web. Se suben a Medios y se eligen en **Caracool → Nueva Visión → Gafas 3D**; el plugin deja subir `.glb` a los administradores. Sin modelo elegido, el módulo no carga nada.

El modelo de la web es el OBJ original (19 MB, 135.000 vértices) simplificado al 12 % y comprimido con meshopt (≈200 KB). El código fuente del módulo 3D está en `fuente/gafas3d.js`; se empaqueta con esbuild:

```
npx esbuild fuente/gafas3d.js --bundle --format=esm --minify --target=es2019 --legal-comments=none --outfile=assets/gafas/gafas-3d.js
```

### Ojo del logo

Pinta el ojo del logotipo en un contenedor. La pupila sigue al cursor (al scroll en táctil) y el ojo parpadea cada pocos segundos. Se activa en un contenedor vacío: **Estilo → Nueva Visión · Ojo del logo**. Colores del trazo y del brillo con el selector nativo; sin elegir, Secundario y Acento del Kit. No necesita ningún widget HTML.

Se para fuera de pantalla y con la pestaña oculta; con movimiento reducido, quieto. `cnv-ojo.css` y `cnv-ojo.js` (≈2 KB) solo se cargan donde se usa.

## Lo que no está aquí

El schema de la óptica sigue en OneStep (Código), como en el resto de webs.

## Estructura

```
caracool-nuevavision/
├── caracool-nuevavision.php   Archivo principal: ajustes, pestañas, actualizaciones
├── inc/caracool-menu.php      Menú compartido de Caracool
├── modules/
│   ├── cnv-gafas.php          Gafas 3D
│   └── cnv-ojo.php            Ojo del logo
├── assets/
│   ├── cnv-gafas.css / .js    Capa, imagen fija y cargador diferido
│   ├── cnv-ojo.css / .js      Ojo, mirada y parpadeo
│   └── gafas/
│       ├── gafas-3d.js        Three.js + módulo, empaquetado
│       └── LICENSE-three.txt  Licencia MIT de Three.js
└── fuente/gafas3d.js          Fuente del módulo 3D
```
