<?php
session_start();
require_once __DIR__ . "/assets/sentenciasSQL/conexion.php";
require_once __DIR__ . "/assets/sentenciasSQL/claseUsuarios.php";

$error = "";

if (isset($_POST['iniciar'])) {
    $usuario = trim($_POST['usuario']); // Puede ser correo o matrícula
    $contrasena = trim($_POST['contrasena']); // Contraseña o CURP

    if (empty($usuario) || empty($contrasena)) {
        $error = "⚠️ Todos los campos son obligatorios.";
    } else {

        // ======= ADMIN =======
        $admin = new Admin();
        $adminData = $admin->leerAdmin($usuario, $contrasena);
        if ($adminData) {
            $_SESSION['rol'] = 'admin';
            $_SESSION['idAdmin'] = $adminData['id_admin'];
            $_SESSION['nombre'] = $adminData['nombre'];
            header("Location: administrador/menuGrupos.php");
            exit();
        }

        // ======= PROFESOR =======
$profesor = new Profesor();
$profesorData = $profesor->leerProfesor($usuario, $contrasena);

if ($profesorData) {
    $_SESSION['rol'] = 'profesor';
    $_SESSION['idProfesor'] = $profesorData['id_profesor'];
    $_SESSION['correo'] = $profesorData['correo'];
    $_SESSION['nombre'] = $profesorData['nombre']; // opcional para mostrar en la interfaz
    header("Location: Docentes/menuDocente.php");
    exit();
} 
        // ======= ALUMNO =======
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
                <label for="usuario" class="tex">Correo o Matrícula:</label><br>
                <input type="text" name="usuario" id="usuario" class="TEXTO" required>
            </div>
            <br>
            <div class="input-box">
                <label for="contrasena" class="tex">Contraseña o CURP:</label><br>
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
