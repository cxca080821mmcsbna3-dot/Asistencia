<?php
session_start();

if (!isset($_SESSION['idAlumno']) || $_SESSION['rol'] !== 'alumno') {
    header("Location: ../index.php");
    exit;
}

$nombreAlumno = $_SESSION['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menú Alumno</title>
  <link rel="stylesheet" href="css/menu_alumno.css?v=1.0">
</head>
<body>

<div class="contenedor">
  <div class="contenedor1">
    <video autoplay muted loop id="videoFondo">
      <source src="img/video.mp4" type="video/mp4">
    </video>

    <header>
      <label class="cecytem"><img src="img/CECYTEM.PNG" alt="Incorrecto">  CECYTEM</label>
      <nav class="navbar">
        <a href="asistencia.php">Asistencia</a>
        <a href="Perfil.php">Perfil</a>
        <a href="cerrar.php">Cerrar sesión</a>
      </nav>

      <!-- Switch para modo oscuro -->
      <label class="switch">
        <input type="checkbox" id="modoOscuro">
        <span class="slider"></span>
      </label>
    </header>

    <div class="saludo">
      <h1>Bienvenid@ <?php echo htmlspecialchars($nombreAlumno); ?></h1>
      <p>Explora tu espacio de alumno y sigue avanzando.</p>
    </div>
  </div>

  <div class="contenedor2">
    <h2>Calendario Escolar 2025</h2><br>
    <img src="img/calendario.jpeg" alt=""><br><br>
    <br><br>

    <h2>Prueba COHEFICIENTE INTELECTUAL</h2><br>
    <h4>¿que tan listo sientes que eres?</h4>
    <a href="https://iq.mental-impulse.com/es/flow/iq_test_s1_es?utm_source=google&utm_medium=cpc&utm_campaign=Search_iq_test_s1_es_SP_countries_YO_170925&utm_term=test%20de%20inteligencia&utm_content=774570667865_b&utm_device=c&utm_adgroup=189263508127&ad_id=774570667865&media_source=google&gad_source=1&gad_campaignid=23032677787&gbraid=0AAAABArMyi-DFwi7By8ZxjSg-cESqmid0&gclid=EAIaIQobChMI19vD7e-TkAMVqy7UAR0pqgQmEAAYASAAEgIIV_D_BwE">
      <img src="img/pruebaCI.png" alt="Incorrecto"></a>
  </div>
</div>

<!-- SCRIPT: Modo oscuro -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.getElementById("modoOscuro");

  // Aplicar modo oscuro si ya estaba activado
  const dark = localStorage.getItem("modo") === "oscuro";
  if (dark) {
    document.body.classList.add("dark-mode");
    toggle.checked = true;
  }

  // Cambiar modo al usar el switch
  toggle.addEventListener("change", () => {
    if (toggle.checked) {
      document.body.classList.add("dark-mode");
      localStorage.setItem("modo", "oscuro");
    } else {
      document.body.classList.remove("dark-mode");
      localStorage.setItem("modo", "claro");
    }
  });
});
</script>

</body>
</html>
