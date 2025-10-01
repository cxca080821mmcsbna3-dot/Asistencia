<?php
session_start();
require_once __DIR__ . "/assets/sentenciasSQL/conexion.php";
require_once __DIR__ . "/assets/sentenciasSQL/docentes.php";

$error = "";

if (isset($_POST['iniciar']) && !empty($_POST['usuario']) && !empty($_POST['contrasena'])) {
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);

    $profesor = new Profesor();
    $profesorData = $profesor->buscarPorNombreYCorreo($usuario, $contrasena);
    if ($profesorData) {
        $_SESSION['rol'] = 'profesor';
        $_SESSION['idProfesor'] = $profesorData['id_profesor'];
        $_SESSION['nombre'] = $profesorData['nombre'];
        header("Location: menuDocente.php");
        exit();
    }

    $error = "Datos incorrectos.";
} elseif (isset($_POST['iniciar'])) {
    $error = "Todos los campos son obligatorios.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Docente</title>
    <link rel="stylesheet" href="assets/css/indexD.css">
</head>
<body>
    <div class="container">
        <h1 class="heading">Login</h1>
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form class="form" action="" method="post">
            <label for="usuario">Nombre completo:</label>
            <input type="text" class="input" name="usuario" id="usuario" placeholder="Ingresa tu nombre" required>
<br><br>
            <label for="contrasena">Contraseña (correo del profesor):</label>
            <input type="text" class="input" name="contrasena" id="contrasena" placeholder="Ingresa tu correo" required>

            <button type="submit" name="iniciar" class="login-button">Iniciar sesión</button>
        </form>
    </div>
</body>
</html>
