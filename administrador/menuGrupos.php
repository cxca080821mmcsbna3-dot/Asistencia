<?php
session_start();

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nombreAdmin = $_SESSION['nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menú Alumno</title>
  <link rel="stylesheet" href="css/menu.css?v=2.2">
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
        <a href="gruposCreados.php" class="boton">Grupos</a>
        <a href="agregar_materia.php" class="boton">Materias</a>
        <a href="usuarios.php" class="boton">Usuarios</a>
        <a href="auditoria.php" class="boton">Auditoria</a>
        <a href="asignarClase.php" class="boton">Asignar Clase</a>
        <a href="alumnos.php" class="boton">Asignacion de grupos</a>
        <a href="../assets/sentenciasSQL/logout.php" class="boton">Cerrar sesión</a>  <!-- Aquí va el botón -->
       </nav>

      <!-- Switch para modo oscuro -->
      <label class="switch">
        <input type="checkbox" id="modoOscuro">
        <span class="slider"></span>
      </label>
    </header>

    <div class="saludo">
      <h1>Bienvenid@ <?php echo htmlspecialchars($nombreAdmin); ?></h1>
      <p>Explora tu espacio de Administrador y sigue avanzando.</p>
    </div>
  </div>

  <div class="contenedor2">
    <h2>Calendario Escolar 2025</h2><br>
    <img src="img/calendario.jpeg" alt=""><br><br>
    <br><br>

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
