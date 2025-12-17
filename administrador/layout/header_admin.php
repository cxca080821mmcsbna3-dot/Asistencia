<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nombreAdmin = $_SESSION['nombre'];
?>
<style>
  /* ======== NAVBAR ======== */
header {
  position: fixed;
  top: 0;
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 25px 60px;
  background: rgba(0,0,0,0.3);
  backdrop-filter: blur(8px);
}

/* Evita que el header fijo tape el contenido de la página */
body {
  padding-top: 140px !important; /* ajustar si el header cambia de alto */
  font-family: "Segoe UI", sans-serif;
  background: var(--fondo2);
}

label img{
  height: 60px;
  margin: 0%;
}

.cecytem {
  font-size: 1.8em;
  font-weight: bold;
  color: #fff;
  letter-spacing: 2px;
  margin: 0%;
}

.navbar a {
  color: #fff;
  margin: 0 15px;
  text-decoration: none;
  font-size: 1.1em;
  transition: color 0.3s ease;
}

.navbar a:hover {
  color: #ffb300;
}

/* ======== Saldo ======== */

.saludo h1 {
  font-size: 3em;
  margin-bottom: 10px;
  text-shadow: 0 2px 8px rgba(0,0,0,0.6);
}

.saludo p {
  font-size: 1.2em;
  color: #f0f0f0;
}

#cambiarColor {
  margin-left: 20px;
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  background: #ffb300;
  color: white;
  font-weight: bold;
  transition: background 0.3s;
}
#cambiarColor:hover {
  background: #ffa000;
}


/* ======== 2 ======== */
.contenedor2 {
  min-height: 100vh;
  background: #f4f6f9;
  color: #333;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 60px 20px;
  text-align: center;
}

.contenedor2 h2 {
  font-size: 2.5em;
  margin-bottom: 20px;
}

.contenedor2 img {
  width: 300px;
  border-radius: 10px;
  margin-top: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/*Modo oscuro*/

/* Estilos por defecto (modo claro) */
:root {
  --fondo1: rgba(0,0,0,0.3);
  --texto1: #ffffff;
  --fondo2: #f4f6f9;
  --texto2: #333333;
}

/* Modo oscuro */
.dark-mode {
  --fondo1: rgba(0,0,0,0.7);
  --texto1: #f5f5f5;
  --fondo2: #121212;
  --texto2: #e0e0e0;
}

/* Switch toggle */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
  margin-left: 20px;
}
.switch input { display: none; }
.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0;
  right: 0; bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 24px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 18px; width: 18px;
  left: 3px; bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #ffb300;
}
input:checked + .slider:before {
  transform: translateX(26px);
}

/* Variables base */
:root {
  --fondo1: rgba(0,0,0,0.1);
  --texto1: #000;
  --fondo2: #f4f6f9;
  --texto2: #333;
}

.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}

.switch input { display:none; }

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  border-radius: 24px;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px; width: 18px;
  left: 3px; bottom: 3px;
  background-color: white;
  border-radius: 50%;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:checked + .slider:before {
  transform: translateX(26px);
}

/* =================== SALUDO =================== */
.saludo h1 {
  font-size: 2.5rem;
  color: #fff;
  text-shadow: 1px 1px 5px rgba(0,0,0,0.5);
}

.saludo p {
  font-size: 1.2rem;
  color: #fff;
  text-shadow: 1px 1px 5px rgba(0,0,0,0.5);
}

/* =================== MODO OSCURO =================== */
body.dark-mode {
  background-color: #121212;
  color: #e0e0e0;
}

body.dark-mode header {
  background-color: rgba(30,30,30,0.9);
}

body.dark-mode .navbar a {
  color: #e0e0e0;
}

body.dark-mode .contenedor2 {
  background-color: #1e1e1e;
  box-shadow: 0 0 10px rgba(255,255,255,0.1);
}

body.dark-mode .saludo h1,
body.dark-mode .saludo p {
  color: #fff;
  text-shadow: 1px 1px 5px rgba(0,0,0,0.7);
}

</style>
<!-- HEADER GLOBAL -->
<header>
  <label class="cecytem">
    <img src="img/CECYTEM.PNG" alt="Incorrecto"> CECYTEM
  </label>

  <nav class="navbar">
    <a href="gruposCreados.php" class="boton">Grupos</a>
    <a href="agregar_materia.php" class="boton">Materias</a>
    <a href="usuarios.php" class="boton">Usuarios</a>
    <a href="auditoria.php" class="boton">Auditoria</a>
    <a href="asignarClase.php" class="boton">Asignar Clase</a>
    <a href="alumnos.php" class="boton">Asignacion de grupos</a>
    <a href="../assets/sentenciasSQL/logout.php" class="boton">Cerrar sesión</a>
  </nav>

  <!-- Switch modo oscuro -->
  <label class="switch">
    <input type="checkbox" id="modoOscuro">
    <span class="slider"></span>
  </label>
</header>

<!-- CARGA AUTOMÁTICA DEL JS (solo aquí) -->
<script src="js/modo_oscuro.js"></script>
<!-- Ajustar padding-top del body según la altura real del header para que no tape el contenido -->
<script>
  (function(){
    function ajustarPadding(){
      var h = document.querySelector('header');
      if(!h) return;
      // Asignar padding-top igual a la altura del header
      document.body.style.paddingTop = h.offsetHeight + 'px';
    }

    // Ejecutar al cargar y al cambiar el tamaño de ventana
    window.addEventListener('load', ajustarPadding);
    window.addEventListener('resize', ajustarPadding);

    // Si el navegador soporta ResizeObserver, observar cambios en el header
    if (window.ResizeObserver) {
      try {
        new ResizeObserver(ajustarPadding).observe(document.querySelector('header'));
      } catch (e) {
        // Si hay algún error, simplemente no usamos ResizeObserver
        console.warn('ResizeObserver no disponible:', e);
      }
    }
  })();
</script>
