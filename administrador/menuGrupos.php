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
  <link rel="stylesheet" href="css/menu.css?v=2.1">
</head>

<body>

<div class="contenedor">

  <div class="contenedor1">
    <video autoplay muted loop id="videoFondo">
      <source src="img/video.mp4" type="video/mp4">
    </video>

    <!-- LLAMAMOS AL HEADER GLOBAL -->
    <?php include_once "layout/header_admin.php"; ?>

    <div class="saludo">
      <h1>Bienvenid@ <?php echo htmlspecialchars($nombreAdmin); ?></h1>
      <p>Explora tu espacio de Administrador y sigue avanzando.</p>
    </div>
  </div>

  <div class="contenedor2">
    <h2>Calendario Escolar 2025</h2><br>
    <img src="img/calendario.jpeg" alt=""><br><br>
  </div>

</div>

</body>
  <?php include_once "layout/footer_admin.php"; ?>
</html>
