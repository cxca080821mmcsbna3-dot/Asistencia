<?php
session_start();

if (!isset($_SESSION['ALUMNO'])) {
    header("Location: ../index.php");
    exit;
}

// ✔ NO intentes leer id_alumno si no existe
$nombreAlumno = $_SESSION['ALUMNO']['apellidos'] . ' ' . $_SESSION['ALUMNO']['nombre'];
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menú Alumno</title>
  <link rel="stylesheet" href="css/menu_alumno.css?v=2.2">
</head>
<body>

<div class="contenedor">
  <div class="contenedor1">
    <video autoplay muted loop id="videoFondo">
      <source src="img/video.mp4" type="video/mp4">
    </video>

    <header>
      <label class="cecytem">
        <img src="img/CECYTEM.PNG" alt="Incorrecto"> CECYTEM
      </label>

      <nav class="navbar">
        <a href="asistencia.php">Asistencia</a>
        <a href="perfil.php">Perfil</a>
        <a href="cerrar.php">Cerrar sesión</a>
      </nav>

      <!-- Switch para modo oscuro -->
      <label class="switch">
        <input type="checkbox" id="modoOscuro">
        <span class="slider"></span>
      </label>
    </header>

    <div class="saludo">
      <h1>Bienvenid@ <?= htmlspecialchars($nombreAlumno) ?></h1>
      <p>Explora tu espacio de alumno y sigue avanzando.</p>
    </div>
  </div>

  <div class="contenedor2">
    <h2>Calendario Escolar 2025</h2><br>
    <img src="img/calendario.jpeg" alt=""><br><br>
  </div>
</div>

<!-- SCRIPT: Modo oscuro -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.getElementById("modoOscuro");

  const dark = localStorage.getItem("modo") === "oscuro";
  if (dark) {
    document.body.classList.add("dark-mode");
    toggle.checked = true;
  }

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
