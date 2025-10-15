<?php
session_start();
require_once __DIR__ . "../assets/sentenciasSQL/conexion.php";
require_once __DIR__ . "../assets/sentenciasSQL/claseUsuarios.php";

$error = "";

if (isset($_POST['iniciar'])) {

    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);

    if (empty($usuario) || empty($contrasena)) {
        $error = "⚠️ Todos los campos son obligatorios.";
    } else {

        // ========= ADMIN =========
        if (preg_match('/^[A-Za-z0-9._%+-]+@soycecytem\.mx$/i', $usuario)) {
            $admin = new Admin();
            $adminData = $admin->leerAdmin($usuario, $contrasena);

            if ($adminData) {
                $_SESSION['rol'] = 'admin';
                $_SESSION['idAdmin'] = $adminData['idAdmin'];
                $_SESSION['usuario'] = $adminData['usuario'];
                header("Location: administrador/menuGrupos.php");
                exit();
            }
        }

        // ========= PROFESOR =========
        if (preg_match('/^[A-Za-z0-9._%+-]+@soycecytem\.mx$/i', $usuario)) {
            $profesor = new Profesor();
            $profesorData = $profesor->buscarPorCorreo($usuario); // Nuevo método que busca solo por correo

            if ($profesorData && password_verify($contrasena, $profesorData['password'])) {
                $_SESSION['rol'] = 'profesor';
                $_SESSION['idProfesor'] = $profesorData['id_profesor'];
                $_SESSION['nombre'] = $profesorData['nombre'];
                header("Location: Docentes/menuDocente.php");
                exit();
            }
        }

        // ========= ALUMNO =========
        if (preg_match('/^[A-Za-z0-9]+$/i', $usuario)) { // matrícula = solo letras y números
            $alumno = new Alumno();
            $alumnoData = $alumno->buscarPorMatriculaYCurp($usuario, $contrasena);

            if ($alumnoData) {
                $_SESSION['rol'] = 'alumno';
                $_SESSION['idAlumno'] = $alumnoData['id_alumno'];
                $_SESSION['nombre'] = $alumnoData['nombre'];
                $_SESSION['matricula'] = $alumnoData['matricula'];
                header("Location: alumno/menu_alumno.php");
                exit();
            }
        }

        // Si no coincide con ninguno
        $error = "❌ Credenciales incorrectas o usuario no registrado.";
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=123">
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
                <input type="password" name="contrasena" id="contrasena" class="TEXTO" required>
            </div>
            <br>
            <div class="button-group">
                <button type="submit" name="iniciar" class="btn">Iniciar sesión</button>
            </div>
        </form>
    </div>

</body>
</html>
