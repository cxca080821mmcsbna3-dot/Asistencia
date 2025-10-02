<?php
session_start();

if (!isset($_SESSION['idAlumno']) || $_SESSION['rol'] !== 'alumno') {
    header("Location: ../login.php");
    exit();
}

$nombreAlumno = $_SESSION['nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú del Alumno</title>
    <link rel="stylesheet" href="css/menu_alumno.css">
</head>
<body>

<div class="grid-container">
  
  <!-- Menú lateral -->
  <nav class="menu">
    <div class="saludo">
      <p>Hola <strong><?php echo htmlspecialchars($nombreAlumno); ?></strong></p>
      <p>Bienvenido</p>
    </div>

    <a href="#">Asistencia</a>
    <a href="#">Perfil</a>
    <a href="cerrar.php" class="boton">Cerrar sesión</a>
  </nav>

  <!-- Contenido principal -->
  <main class="contenido">
    <h1></h1>
    <p></p>
  </main>

</div>

</body>
</html>
