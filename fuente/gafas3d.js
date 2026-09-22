/* Caracool Nueva Visión · gafas 3D (se empaqueta con esbuild en assets/gafas/gafas-3d.js)
   Carga el modelo, lo ilumina en tonos de la marca y lo gira con el scroll.
   Pinta solo cuando está en pantalla. */
import {
  WebGLRenderer, Scene, PerspectiveCamera, Group, Box3, Vector3,
  MeshPhysicalMaterial, MeshStandardMaterial, HemisphereLight, DirectionalLight,
  PMREMGenerator, ACESFilmicToneMapping, SRGBColorSpace, Color, MathUtils
} from 'three';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
import { MeshoptDecoder } from 'three/examples/jsm/libs/meshopt_decoder.module.js';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';

export function gafas3d(opts) {
  const { lienzo, zona, glb, alListo, reducido } = opts;
  const giro = typeof opts.giro === 'number' ? opts.giro : 1;
  const renderer = new WebGLRenderer({ canvas: lienzo, antialias: true, alpha: true, powerPreference: 'low-power' });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
  renderer.toneMapping = ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.05;
  renderer.outputColorSpace = SRGBColorSpace;

  const escena = new Scene();
  const pmrem = new PMREMGenerator(renderer);
  escena.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
  escena.add(new HemisphereLight(0xfff2f6, 0xd27796, 1.1));
  const clave = new DirectionalLight(0xffffff, 1.6); clave.position.set(3, 4, 5); escena.add(clave);
  const borde = new DirectionalLight(0xf7a8c4, 2.2); borde.position.set(-4, 1, -3); escena.add(borde);

  const camara = new PerspectiveCamera(26, 1, 0.1, 100);
  camara.position.set(0, 0, 9);

  const pivote = new Group(); escena.add(pivote);

  const materiales = {
    montura: new MeshPhysicalMaterial({ color: new Color('#1c1a21'), roughness: 0.24, metalness: 0, clearcoat: 1, clearcoatRoughness: 0.08, envMapIntensity: 1.2 }),
    lente: new MeshPhysicalMaterial({ color: new Color('#fff4f8'), roughness: 0.02, metalness: 0, transparent: true, opacity: 0.2, envMapIntensity: 2.2, clearcoat: 1, depthWrite: false }),
    metal: new MeshStandardMaterial({ color: new Color('#e7e3e6'), roughness: 0.2, metalness: 1 })
  };
  const tipo = { Cube: 'lente', Cube001: 'montura', Cube002: 'montura', Cylinder: 'metal', Cube003: 'metal' };

  const cargador = new GLTFLoader(); cargador.setMeshoptDecoder(MeshoptDecoder);
  let modelo = null;
  cargador.parse(glb, '', (g) => {
    modelo = g.scene;
    let frente = null;
    modelo.traverse((o) => {
      if (!o.isMesh) return;
      o.material = materiales[tipo[o.name] || 'montura'];
      if (tipo[o.name] === 'lente') o.renderOrder = 2;
      if (o.name === 'Cube001') frente = o;
    });
    // el modelo mira a +X: se gira para que mire a cámara y se centra en la montura
    modelo.rotation.y = -Math.PI / 2;
    const tmp = new Group(); tmp.add(modelo); tmp.updateMatrixWorld(true);
    const caja = new Box3().setFromObject(frente || modelo);
    const centro = caja.getCenter(new Vector3());
    const ancho = caja.getSize(new Vector3()).x;
    modelo.position.sub(centro);
    const escala = 2.6 / ancho;
    tmp.scale.setScalar(escala);
    pivote.add(tmp);
    medir(); pintar();
    alListo && alListo();
  });

  // pose: al llegar, tres cuartos como el render; con el scroll gira y se va
  const P0 = { rx: 0.32, ry: 0.62, rz: -0.08 };
  const P1 = { rx: P0.rx + (-0.52) * giro, ry: P0.ry + (-2.92) * giro, rz: P0.rz + 0.33 * giro };
  let progreso = 0, px = 0, py = 0, sx = 0, sy = 0, visible = true, t0 = performance.now(), raf = 0;

  function medir() {
    const r = zona.getBoundingClientRect();
    renderer.setSize(r.width, r.height, false);
    camara.aspect = r.width / Math.max(1, r.height);
    camara.updateProjectionMatrix();
  }
  function pintar(t) {
    if (!modelo) return;
    const s = ((t || performance.now()) - t0) / 1000;
    sx += (px - sx) * 0.06; sy += (py - sy) * 0.06;
    const e = progreso < 0.5 ? 2 * progreso * progreso : 1 - Math.pow(-2 * progreso + 2, 2) / 2;
    const flota = reducido ? 0 : Math.sin(s * 0.9) * 0.05;
    pivote.rotation.set(
      MathUtils.lerp(P0.rx, P1.rx, e) + sy * 0.18 + (reducido ? 0 : Math.sin(s * 0.7) * 0.03),
      MathUtils.lerp(P0.ry, P1.ry, e) + sx * 0.3,
      MathUtils.lerp(P0.rz, P1.rz, e)
    );
    pivote.position.y = flota + e * 0.25;
    renderer.render(escena, camara);
  }
  function bucle(t) { raf = 0; if (!visible) return; pintar(t); raf = requestAnimationFrame(bucle); }
  function arrancar() { if (!raf && visible && !reducido) raf = requestAnimationFrame(bucle); }

  new IntersectionObserver((e) => { visible = e[0].isIntersecting && !document.hidden; if (visible) arrancar(); }).observe(zona);
  document.addEventListener('visibilitychange', () => { visible = !document.hidden; if (visible) arrancar(); });
  window.addEventListener('resize', () => { medir(); pintar(); });
  if (!reducido && window.matchMedia('(hover: hover)').matches) {
    window.addEventListener('pointermove', (e) => { px = e.clientX / innerWidth * 2 - 1; py = e.clientY / innerHeight * 2 - 1; }, { passive: true });
  }
  medir(); arrancar();

  return {
    progreso(p) { progreso = Math.max(0, Math.min(1, p)); if (reducido) pintar(); }
  };
}
