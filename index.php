<?php
session_start();
require_once __DIR__ . "/assets/sentenciasSQL/conexion.php";
require_once __DIR__ . "/assets/sentenciasSQL/claseUsuarios.php";


$error = "";

if (isset($_POST['iniciar']) && !empty($_POST['usuario']) && !empty($_POST['contrasena'])) {
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);

    $admin = new Admin();
    $adminData = $admin->leerAdmin($usuario, $contrasena);
    if ($adminData) {
        $_SESSION['rol'] = 'admin';
        $_SESSION['idAdmin'] = $adminData['idAdmin'];
        $_SESSION['usuario'] = $adminData['usuario'];
        header("Location: menuGrupos.php");
        exit();
    }

    $profesor = new Profesor();
    $profesorData = $profesor->buscarPorNombreYCorreo($usuario, $contrasena);
    if ($profesorData) {
        $_SESSION['rol'] = 'profesor';
        $_SESSION['idProfesor'] = $profesorData['id_profesor'];
        $_SESSION['nombre'] = $profesorData['nombre'];
        header("Location: menuGrupos.php");
        exit();
    }

    $alumno = new Alumno();
    $alumnoData = $alumno->buscarPorNombreYCorreo($usuario, $contrasena);
    if ($alumnoData) {
        $_SESSION['rol'] = 'alumno';
        $_SESSION['idAlumno'] = $alumnoData['id_alumno'];
        $_SESSION['nombre'] = $alumnoData['nombre'];
        header("Location: menuGrupos.php");
        exit();
    }

    $error = "Credenciales incorrectas.";
} elseif (isset($_POST['iniciar'])) {
    $error = "Todos los campos son obligatorios.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1>Login</h1>
            <?php if (!empty($error)): ?>
                <p style="color:cornflowerblue; text-align:center;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <div class="input-box">
                <label for="usuario" class="tex">Nombre completo o usuario:</label><br>
                <input type="text" name="usuario" id="usuario" class="TEXTO" required>
            </div>
            <br>
            <div class="input-box">
                <label for="contrasena" class="tex">Contraseña (correo para alumno/profesor):</label><br>
                <input type="text" name="contrasena" id="contrasena" class="TEXTO" required>
            </div>
            <br>
            <div class="button-group">
                <button type="submit" name="iniciar" class="btn">Iniciar sesión</button>
            </div>
        </form>
    </div>
</body>
</html>
